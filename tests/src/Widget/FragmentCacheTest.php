<?php

namespace leapsunit\src\Widget;

use Leaps;
use Leaps\Cache\ArrayCache;
use Leaps\Base\View;
use Leaps\Widget\Breadcrumbs;

/**
 * @group widgets
 * @group caching
 */
class FragmentCacheTest extends \leapsunit\TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockWebApplication();
        Leaps::$app->set('cache', [
            'className' => ArrayCache::className(),
        ]);
    }

    public function testCacheEnabled()
    {
        $expectedLevel = ob_get_level();
        ob_start();
        ob_implicit_flush(false);

        $view = new View();
        $this->assertTrue($view->beginCache('test'));
        echo "cached fragment";
        $view->endCache();

        ob_start();
        ob_implicit_flush(false);
        $this->assertFalse($view->beginCache('test'));
        $this->assertEquals("cached fragment", ob_get_clean());

        ob_end_clean();
        $this->assertEquals($expectedLevel, ob_get_level(), 'Output buffer not closed correctly.');
    }

    public function testCacheDisabled1()
    {
        $expectedLevel = ob_get_level();
        ob_start();
        ob_implicit_flush(false);

        $view = new View();
        $this->assertTrue($view->beginCache('test', ['enabled' => false]));
        echo "cached fragment";
        $view->endCache();

        ob_start();
        ob_implicit_flush(false);
        $this->assertTrue($view->beginCache('test', ['enabled' => false]));
        echo "cached fragment";
        $view->endCache();
        $this->assertEquals("cached fragment", ob_get_clean());

        ob_end_clean();
        $this->assertEquals($expectedLevel, ob_get_level(), 'Output buffer not closed correctly.');
    }

    public function testCacheDisabled2()
    {
        $expectedLevel = ob_get_level();
        ob_start();
        ob_implicit_flush(false);

        $view = new View();
        $this->assertTrue($view->beginCache('test'));
        echo "cached fragment";
        $view->endCache();

        ob_start();
        ob_implicit_flush(false);
        $this->assertTrue($view->beginCache('test', ['enabled' => false]));
        echo "cached fragment other";
        $view->endCache();
        $this->assertEquals("cached fragment other", ob_get_clean());

        ob_end_clean();
        $this->assertEquals($expectedLevel, ob_get_level(), 'Output buffer not closed correctly.');
    }


    // TODO test dynamic replacements
}
