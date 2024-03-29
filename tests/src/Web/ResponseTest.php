<?php

namespace leapsunit\src\Web;

use Leaps;
use Leaps\Helper\StringHelper;

/**
 * @group web
 */
class ResponseTest extends \leapsunit\TestCase
{
    /**
     * @var \Leaps\Web\Response
     */
    public $response;

    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
        $this->response = new \Leaps\Web\Response;
    }

    public function rightRanges()
    {
        // TODO test more cases for range requests and check for rfc compatibility
        // http://www.w3.org/Protocols/rfc2616/rfc2616.txt
        return [
            ['0-5', '0-5', 6, '12ёж'],
            ['2-', '2-66', 65, 'ёжик3456798áèabcdefghijklmnopqrstuvwxyz!"§$%&/(ёжик)=?'],
            ['-12', '55-66', 12, '(ёжик)=?'],
        ];
    }

    /**
     * @dataProvider rightRanges
     */
    public function testSendFileRanges($rangeHeader, $expectedHeader, $length, $expectedContent)
    {
        $dataFile = \Leaps::getAlias('@leapsunit/data/web/data.txt');
        $fullContent = file_get_contents($dataFile);
        $_SERVER['HTTP_RANGE'] = 'bytes=' . $rangeHeader;
        ob_start();
        $this->response->sendFile($dataFile)->send(	);
        $content = ob_get_clean();

        $this->assertEquals($expectedContent, $content);
        $this->assertEquals(206, $this->response->statusCode);
        $headers = $this->response->headers;
        $this->assertEquals("bytes", $headers->get('Accept-Ranges'));
        $this->assertEquals("bytes " . $expectedHeader . '/' . StringHelper::byteLength($fullContent), $headers->get('Content-Range'));
        $this->assertEquals('text/plain', $headers->get('Content-Type'));
        $this->assertEquals("$length", $headers->get('Content-Length'));
    }

    public function wrongRanges()
    {
        // TODO test more cases for range requests and check for rfc compatibility
        // http://www.w3.org/Protocols/rfc2616/rfc2616.txt
        return [
            ['1-2,3-5,6-10'],	// multiple range request not supported
            ['5-1'],			// last-byte-pos value is less than its first-byte-pos value
            ['-100000'],		// last-byte-pos bigger then content length
            ['10000-'],			// first-byte-pos bigger then content length
        ];
    }

    /**
     * @dataProvider wrongRanges
     */
    public function testSendFileWrongRanges($rangeHeader)
    {
        $this->setExpectedException('Leaps\Web\HttpException');

        $dataFile = \Leaps::getAlias('@leapsunit/data/web/data.txt');
        $_SERVER['HTTP_RANGE'] = 'bytes=' . $rangeHeader;
        $this->response->sendFile($dataFile);
    }

    protected function generateTestFileContent()
    {
        return '12ёжик3456798áèabcdefghijklmnopqrstuvwxyz!"§$%&/(ёжик)=?';
    }

    /**
     * https://github.com/yiisoft/yii2/issues/7529
     */
    public function testSendContentAsFile()
    {
        ob_start();
        $this->response->sendContentAsFile('test', 'test.txt')->send([
            'mimeType' => 'text/plain'
        ]);
        $content = ob_get_clean();

        static::assertEquals('test', $content);
        static::assertEquals(200, $this->response->statusCode);
        $headers = $this->response->headers;
        static::assertEquals('application/octet-stream', $headers->get('Content-Type'));
        static::assertEquals('attachment; filename="test.txt"', $headers->get('Content-Disposition'));
        static::assertEquals(4, $headers->get('Content-Length'));
    }
}
