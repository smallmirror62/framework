<?php
/**
 * @author Carsten Brandt <mail@cebe.cc>
 */

namespace leapsunit\src\Web;

use Leaps\Helper\FileHelper;
use Leaps\Web\AssetConverter;

/**
 * @group web
 */
class AssetConverterTest extends \leapsunit\TestCase
{
    /**
     * @var string temporary files path
     */
    protected $tmpPath;

    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
        $this->tmpPath = \Leaps::$app->runtimePath . '/assetConverterTest_' . getmypid();
        if (!is_dir($this->tmpPath)) {
            mkdir($this->tmpPath, 0777, true);
        }
    }

    protected function tearDown()
    {
        if (is_dir($this->tmpPath)) {
            FileHelper::removeDirectory($this->tmpPath);
        }
        parent::tearDown();
    }

    // Tests :

    public function testConvert()
    {
        $tmpPath = $this->tmpPath;
        file_put_contents($tmpPath . '/test.php', <<<EOF
<?php

echo "Hello World!\n";
echo "Hello Leaps!";
EOF
        );

        $converter = new AssetConverter();
        $converter->commands['php'] = ['txt', 'php {from} > {to}'];
        $this->assertEquals('test.txt', $converter->convert('test.php', $tmpPath));

        $this->assertTrue(file_exists($tmpPath . '/test.txt'), 'Failed asserting that asset output file exists.');
        $this->assertEquals("Hello World!\nHello Leaps!", file_get_contents($tmpPath . '/test.txt'));
    }

    /**
     * @depends testConvert
     */
    public function testForceConvert()
    {
        $tmpPath = $this->tmpPath;
        file_put_contents($tmpPath . '/test.php', <<<EOF
<?php

echo microtime();
EOF
        );

        $converter = new AssetConverter();
        $converter->commands['php'] = ['txt', 'php {from} > {to}'];

        $converter->convert('test.php', $tmpPath);
        $initialConvertTime = file_get_contents($tmpPath . '/test.txt');

        usleep(1);
        $converter->convert('test.php', $tmpPath);
        $this->assertEquals($initialConvertTime, file_get_contents($tmpPath . '/test.txt'));

        $converter->forceConvert = true;
        $converter->convert('test.php', $tmpPath);
        $this->assertNotEquals($initialConvertTime, file_get_contents($tmpPath . '/test.txt'));
    }
}
