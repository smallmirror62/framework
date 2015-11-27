<?php
namespace Leapsunit\framework;

use Leaps;
use leapsunit\TestCase;

/**
 * KernelTest
 * @group base
 */
class KernelTest extends TestCase
{
    public $aliases;

    protected function setUp()
    {
        parent::setUp();
        $this->aliases = Leaps::$aliases;
    }

    protected function tearDown()
    {
        parent::tearDown();
        Leaps::$aliases = $this->aliases;
    }

    public function testAlias()
    {
        $this->assertEquals(LEAPS_PATH, Leaps::getAlias('@leaps'));

        Leaps::$aliases = [];
        $this->assertFalse(Leaps::getAlias('@leaps', false));

        Leaps::setAlias('@leaps', '/Leaps/Framework');
        $this->assertEquals('/leaps/framework', Leaps::getAlias('@leaps'));
        $this->assertEquals('/leaps/framework/test/file', Leaps::getAlias('@leaps/test/file'));
        Leaps::setAlias('@leaps/gii', '/yii/gii');
        $this->assertEquals('/leaps/framework', Leaps::getAlias('@leaps'));
        $this->assertEquals('/leaps/framework/test/file', Leaps::getAlias('@leaps/test/file'));
        $this->assertEquals('/leaps/gii', Leaps::getAlias('@leaps/gii'));
        $this->assertEquals('/leaps/gii/file', Leaps::getAlias('@leaps/gii/file'));

        Leaps::setAlias('@tii', '@leaps/test');
        $this->assertEquals('/leaps/framework/test', Leaps::getAlias('@tii'));

        Leaps::setAlias('@leaps', null);
        $this->assertFalse(Leaps::getAlias('@yii', false));
        $this->assertEquals('/Leaps/Gii/File', Leaps::getAlias('@leaps/gii/file'));

        Leaps::setAlias('@some/alias', '/www');
        $this->assertEquals('/www', Leaps::getAlias('@some/alias'));
    }

    public function testGetVersion()
    {
        $this->assertTrue((boolean) preg_match('~\d+\.\d+(?:\.\d+)?(?:-\w+)?~', Leaps::getVersion()));
    }

    public function testPowered()
    {
        $this->assertTrue(is_string(Leaps::powered()));
    }
}
