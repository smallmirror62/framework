<?php
/**
 *
 *
 * @author Carsten Brandt <mail@cebe.cc>
 */

namespace leapsunit\src\Web;

use Leaps;
use Leaps\Web\View;
use Leaps\Web\AssetBundle;
use Leaps\Web\AssetManager;

/**
 * @group web
 */
class AssetBundleTest extends \leapsunit\TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();

        Leaps::setAlias('@testWeb', '/');
        Leaps::setAlias('@testWebRoot', '@leapsunit/data/web');
    }

    protected function getView()
    {
        $view = new View();
        $view->setAssetManager(new AssetManager([
            'basePath' => '@testWebRoot/assets',
            'baseUrl' => '@testWeb/assets',
        ]));

        return $view;
    }

    public function testRegister()
    {
        $view = $this->getView();

        $this->assertEmpty($view->assetBundles);
        TestSimpleAsset::register($view);
        $this->assertEquals(1, count($view->assetBundles));
        $this->assertArrayHasKey('leapsunit\\src\\Web\\TestSimpleAsset', $view->assetBundles);
        $this->assertTrue($view->assetBundles['leapsunit\\src\\Web\\TestSimpleAsset'] instanceof AssetBundle);

        $expected = <<<EOF
123<script src="/js/jquery.js"></script>4
EOF;
        $this->assertEquals($expected, $view->renderFile('@leapsunit/data/views/rawlayout.php'));
    }

    public function testSimpleDependency()
    {
        $view = $this->getView();

        $this->assertEmpty($view->assetBundles);
        TestAssetBundle::register($view);
        $this->assertEquals(3, count($view->assetBundles));
        $this->assertArrayHasKey('leapsunit\\src\\Web\\TestAssetBundle', $view->assetBundles);
        $this->assertArrayHasKey('leapsunit\\src\\Web\\TestJqueryAsset', $view->assetBundles);
        $this->assertArrayHasKey('leapsunit\\src\\Web\\TestAssetLevel3', $view->assetBundles);
        $this->assertTrue($view->assetBundles['leapsunit\\src\\Web\\TestAssetBundle'] instanceof AssetBundle);
        $this->assertTrue($view->assetBundles['leapsunit\\src\\Web\\TestJqueryAsset'] instanceof AssetBundle);
        $this->assertTrue($view->assetBundles['leapsunit\\src\\Web\\TestAssetLevel3'] instanceof AssetBundle);

        $expected = <<<EOF
1<link href="/files/cssFile.css" rel="stylesheet">23<script src="/js/jquery.js"></script>
<script src="/files/jsFile.js"></script>4
EOF;
        $this->assertEqualsWithoutLE($expected, $view->renderFile('@leapsunit/data/views/rawlayout.php'));
    }

    public function positionProvider()
    {
        return [
            [View::POS_HEAD, true],
            [View::POS_HEAD, false],
            [View::POS_BEGIN, true],
            [View::POS_BEGIN, false],
            [View::POS_END, true],
            [View::POS_END, false],
        ];
    }

    /**
     * @dataProvider positionProvider
     */
    public function testPositionDependency($pos, $jqAlreadyRegistered)
    {
        $view = $this->getView();

        $view->getAssetManager()->bundles['leapsunit\\src\\Web\\TestAssetBundle'] = [
            'jsOptions' => [
                'position' => $pos,
            ],
        ];

        $this->assertEmpty($view->assetBundles);
        if ($jqAlreadyRegistered) {
            TestJqueryAsset::register($view);
        }
        TestAssetBundle::register($view);
        $this->assertEquals(3, count($view->assetBundles));
        $this->assertArrayHasKey('leapsunit\\src\\Web\\TestAssetBundle', $view->assetBundles);
        $this->assertArrayHasKey('leapsunit\\src\\Web\\TestJqueryAsset', $view->assetBundles);
        $this->assertArrayHasKey('leapsunit\\src\\Web\\TestAssetLevel3', $view->assetBundles);

        $this->assertTrue($view->assetBundles['leapsunit\\src\\Web\\TestAssetBundle'] instanceof AssetBundle);
        $this->assertTrue($view->assetBundles['leapsunit\\src\\Web\\TestJqueryAsset'] instanceof AssetBundle);
        $this->assertTrue($view->assetBundles['leapsunit\\src\\Web\\TestAssetLevel3'] instanceof AssetBundle);

        $this->assertArrayHasKey('position', $view->assetBundles['leapsunit\\src\\Web\\TestAssetBundle']->jsOptions);
        $this->assertEquals($pos, $view->assetBundles['leapsunit\\src\\Web\\TestAssetBundle']->jsOptions['position']);
        $this->assertArrayHasKey('position', $view->assetBundles['leapsunit\\src\\Web\\TestJqueryAsset']->jsOptions);
        $this->assertEquals($pos, $view->assetBundles['leapsunit\\src\\Web\\TestJqueryAsset']->jsOptions['position']);
        $this->assertArrayHasKey('position', $view->assetBundles['leapsunit\\src\\Web\\TestAssetLevel3']->jsOptions);
        $this->assertEquals($pos, $view->assetBundles['leapsunit\\src\\Web\\TestAssetLevel3']->jsOptions['position']);

        switch ($pos) {
            case View::POS_HEAD:
                $expected = <<<EOF
1<link href="/files/cssFile.css" rel="stylesheet">
<script src="/js/jquery.js"></script>
<script src="/files/jsFile.js"></script>234
EOF;
            break;
            case View::POS_BEGIN:
                $expected = <<<EOF
1<link href="/files/cssFile.css" rel="stylesheet">2<script src="/js/jquery.js"></script>
<script src="/files/jsFile.js"></script>34
EOF;
            break;
            default:
            case View::POS_END:
                $expected = <<<EOF
1<link href="/files/cssFile.css" rel="stylesheet">23<script src="/js/jquery.js"></script>
<script src="/files/jsFile.js"></script>4
EOF;
            break;
        }
        $this->assertEqualsWithoutLE($expected, $view->renderFile('@leapsunit/data/views/rawlayout.php'));
    }

    public function positionProvider2()
    {
        return [
            [View::POS_BEGIN, true],
            [View::POS_BEGIN, false],
            [View::POS_END, true],
            [View::POS_END, false],
        ];
    }

    /**
     * @dataProvider positionProvider
     */
    public function testPositionDependencyConflict($pos, $jqAlreadyRegistered)
    {
        $view = $this->getView();

        $view->getAssetManager()->bundles['leapsunit\\src\\Web\\TestAssetBundle'] = [
            'jsOptions' => [
                'position' => $pos - 1,
            ],
        ];
        $view->getAssetManager()->bundles['leapsunit\\src\\Web\\TestJqueryAsset'] = [
            'jsOptions' => [
                'position' => $pos,
            ],
        ];

        $this->assertEmpty($view->assetBundles);
        if ($jqAlreadyRegistered) {
            TestJqueryAsset::register($view);
        }
        $this->setExpectedException('Leaps\\Base\\InvalidConfigException');
        TestAssetBundle::register($view);
    }

    public function testCircularDependency()
    {
        $this->setExpectedException('Leaps\\Base\\InvalidConfigException');
        TestAssetCircleA::register($this->getView());
    }

    public function testDuplicateAssetFile()
    {
        $view = $this->getView();

        $this->assertEmpty($view->assetBundles);
        TestSimpleAsset::register($view);
        $this->assertEquals(1, count($view->assetBundles));
        $this->assertArrayHasKey('leapsunit\\src\\Web\\TestSimpleAsset', $view->assetBundles);
        $this->assertTrue($view->assetBundles['leapsunit\\src\\Web\\TestSimpleAsset'] instanceof AssetBundle);
        // register TestJqueryAsset which also has the jquery.js
        TestJqueryAsset::register($view);

        $expected = <<<EOF
123<script src="/js/jquery.js"></script>4
EOF;
        $this->assertEquals($expected, $view->renderFile('@leapsunit/data/views/rawlayout.php'));
    }

    public function testPerFileOptions()
    {
        $view = $this->getView();

        $this->assertEmpty($view->assetBundles);
        TestAssetPerFileOptions::register($view);

        $expected = <<<EOF
1<link href="/default_options.css" rel="stylesheet" media="screen" hreflang="en">
<link href="/tv.css" rel="stylesheet" media="tv" hreflang="en">
<link href="/screen_and_print.css" rel="stylesheet" media="screen, print" hreflang="en">23<script src="/normal.js" charset="utf-8"></script>
<script src="/defered.js" charset="utf-8" defer></script>4
EOF;
        $this->assertEquals($expected, $view->renderFile('@leapsunit/data/views/rawlayout.php'));
    }
}

class TestSimpleAsset extends AssetBundle
{
    public $basePath = '@testWebRoot/js';
    public $baseUrl = '@testWeb/js';
    public $js = [
        'jquery.js',
    ];
}

class TestAssetBundle extends AssetBundle
{
    public $basePath = '@testWebRoot/files';
    public $baseUrl = '@testWeb/files';
    public $css = [
        'cssFile.css',
    ];
    public $js = [
        'jsFile.js',
    ];
    public $depends = [
        'leapsunit\\src\\Web\\TestJqueryAsset'
    ];
}

class TestJqueryAsset extends AssetBundle
{
    public $basePath = '@testWebRoot/js';
    public $baseUrl = '@testWeb/js';
    public $js = [
        'jquery.js',
    ];
    public $depends = [
        'leapsunit\\src\\Web\\TestAssetLevel3'
    ];
}

class TestAssetLevel3 extends AssetBundle
{
    public $basePath = '@testWebRoot/js';
    public $baseUrl = '@testWeb/js';
}

class TestAssetCircleA extends AssetBundle
{
    public $basePath = '@testWebRoot/js';
    public $baseUrl = '@testWeb/js';
    public $js = [
        'jquery.js',
    ];
    public $depends = [
        'leapsunit\\src\\Web\\TestAssetCircleB'
    ];
}

class TestAssetCircleB extends AssetBundle
{
    public $basePath = '@testWebRoot/js';
    public $baseUrl = '@testWeb/js';
    public $js = [
        'jquery.js',
    ];
    public $depends = [
        'leapsunit\\src\\Web\\TestAssetCircleA'
    ];
}

class TestAssetPerFileOptions extends AssetBundle
{
    public $basePath = '@testWebRoot';
    public $baseUrl = '@testWeb';
    public $css = [
        'default_options.css',
        ['tv.css', 'media' => 'tv'],
        ['screen_and_print.css', 'media' => 'screen, print']
    ];
    public $js = [
        'normal.js',
        ['defered.js', 'defer' => true],
    ];
    public $cssOptions = ['media' => 'screen', 'hreflang' => 'en'];
    public $jsOptions = ['charset' => 'utf-8'];
}
