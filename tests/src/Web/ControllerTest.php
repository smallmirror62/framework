<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Leaps Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace leapsunit\src\Web;

use Leaps;
use leapsunit\TestCase;
use leapsunit\src\Di\Stub\Qux;
use leapsunit\src\Web\Stub\Bar;
use leapsunit\src\Web\Stub\OtherQux;
use Leaps\Base\InlineAction;

/**
 * @group web
 */
class ControllerTest extends TestCase
{

    public function testBindActionParams()
    {
        $this->mockApplication([
            'components'=>[
                'barBelongApp'=>[
                    'class'=>  Bar::className(),
                    'foo'=>'belong_app'
                ],
                'quxApp'=>[
                    'class' => OtherQux::className(),
                    'b' => 'belong_app'
                ]
            ]
        ]);

        $controller = new FakeController('fake', Leaps::$app);
        $aksi1 = new InlineAction('aksi1', $controller, 'actionAksi1');
        $aksi2 = new InlineAction('aksi2', $controller, 'actionAksi2');
        $aksi3 = new InlineAction('aksi3', $controller, 'actionAksi3');

        Leaps::$container->set('leapsunit\src\Di\Stub\QuxInterface', [
            'className' => Qux::className(),
            'a' => 'D426'
        ]);
        Leaps::$container->set(Bar::className(),[
            'foo' => 'independent'
        ]);

        $params = ['fromGet'=>'from query params','q'=>'d426','validator'=>'avaliable'];

        list($bar, $fromGet, $other) = $controller->bindActionParams($aksi1, $params);
        $this->assertTrue($bar instanceof Bar);
        $this->assertNotEquals($bar, Leaps::$app->barBelongApp);
        $this->assertEquals('independent', $bar->foo);
        $this->assertEquals('from query params', $fromGet);
        $this->assertEquals('default', $other);

        list($barBelongApp, $qux) = $controller->bindActionParams($aksi2, $params);
        $this->assertTrue($barBelongApp instanceof Bar);
        $this->assertEquals($barBelongApp, Leaps::$app->barBelongApp);
        $this->assertEquals('belong_app', $barBelongApp->foo);
        $this->assertTrue($qux instanceof Qux);
        $this->assertEquals('D426', $qux->a);

        list($quxApp) = $controller->bindActionParams($aksi3, $params);
        $this->assertTrue($quxApp instanceof OtherQux);
        $this->assertEquals($quxApp, Leaps::$app->quxApp);
        $this->assertEquals('belong_app', $quxApp->b);

        $result = $controller->runAction('aksi4', $params);
        $this->assertEquals(['independent', 'other_qux', 'd426'], $result);

        $result = $controller->runAction('aksi5', $params);
        $this->assertEquals(['d426', 'independent', 'other_qux'], $result);

        $result = $controller->runAction('aksi6', $params);
        $this->assertEquals(['d426', false, true], $result);

        // Manually inject an instance of \StdClass
        // In this case we don't want a newly created instance, but use the existing one
        $stdClass = new \StdClass;
        $stdClass->test = 'dummy';
        $result = $controller->runAction('aksi7', array_merge($params, ['validator' => $stdClass]));
        $this->assertEquals(['d426', 'dummy'], $result);

        // Manually inject a string instead of an instance of \StdClass
        // Since this is wrong usage, we expect a new instance of the type hinted \StdClass anyway
        $stdClass = 'string';
        $result = $controller->runAction('aksi8', array_merge($params, ['validator' => $stdClass]));
        $this->assertEquals(['d426', 'object'], $result);
    }
}
