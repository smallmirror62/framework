<?php
/**
 * @author Carsten Brandt <mail@cebe.cc>
 */

namespace leapsunit\src\log;

use Leaps\Helper\FileHelper;
use yii\log\Dispatcher;
use yii\log\Logger;
use Leaps;
use leapsunit\TestCase;

/**
 * @group log
 */
class FileTargetTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    public function booleanDataProvider()
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * @dataProvider booleanDataProvider
     */
    public function testRotate($rotateByCopy)
    {
        $logFile = Leaps::getAlias('@leapsunit/runtime/log/filetargettest.log');
        FileHelper::removeDirectory(dirname($logFile));
        mkdir(dirname($logFile), 0777, true);

        $logger = new Logger();
        $dispatcher = new Dispatcher([
            'logger' => $logger,
            'targets' => [
                'file' => [
                    'class' => 'yii\log\FileTarget',
                    'logFile' => $logFile,
                    'levels' => ['warning'],
                    'maxFileSize' => 1024, // 1 MB
                    'maxLogFiles' => 1, // one file for rotation and one normal log file
                    'logVars' => [],
                    'rotateByCopy' => $rotateByCopy
                ]
            ]
        ]);

        // one file

        $logger->log(str_repeat('x', 1024), Logger::LEVEL_WARNING);
        $logger->flush(true);

        clearstatcache();

        $this->assertTrue(file_exists($logFile));
        $this->assertFalse(file_exists($logFile . '.1'));
        $this->assertFalse(file_exists($logFile . '.2'));
        $this->assertFalse(file_exists($logFile . '.3'));
        $this->assertFalse(file_exists($logFile . '.4'));

        // exceed max size
        for($i = 0; $i < 1024; $i++) {
            $logger->log(str_repeat('x', 1024), Logger::LEVEL_WARNING);
        }
        $logger->flush(true);

        // first rotate

        $logger->log(str_repeat('x', 1024), Logger::LEVEL_WARNING);
        $logger->flush(true);

        clearstatcache();

        $this->assertTrue(file_exists($logFile));
        $this->assertTrue(file_exists($logFile . '.1'));
        $this->assertFalse(file_exists($logFile . '.2'));
        $this->assertFalse(file_exists($logFile . '.3'));
        $this->assertFalse(file_exists($logFile . '.4'));

        // second rotate

        for($i = 0; $i < 1024; $i++) {
            $logger->log(str_repeat('x', 1024), Logger::LEVEL_WARNING);
        }
        $logger->flush(true);

        clearstatcache();

        $this->assertTrue(file_exists($logFile));
        $this->assertTrue(file_exists($logFile . '.1'));
        $this->assertFalse(file_exists($logFile . '.2'));
        $this->assertFalse(file_exists($logFile . '.3'));
        $this->assertFalse(file_exists($logFile . '.4'));
    }
}