<?php

namespace leapsunit\src\Base;

use Leaps\Base\Behavior;
use Leaps\Base\Service;
use leapsunit\TestCase;

class BarClass extends Service
{
}

class FooClass extends Service
{
    public function behaviors()
    {
        return [
            'foo' => __NAMESPACE__ . '\BarBehavior',
        ];
    }
}

class BarBehavior extends Behavior
{
    public $behaviorProperty = 'behavior property';

    public function behaviorMethod()
    {
        return 'behavior method';
    }

    public function __call($name, $params)
    {
        if ($name == 'magicBehaviorMethod') {
            return 'Magic Behavior Method Result!';
        }

        return parent::__call($name, $params);
    }

    public function hasMethod($name)
    {
        if ($name == 'magicBehaviorMethod') {
            return true;
        }

        return parent::hasMethod($name);
    }
}

/**
 * @group base
 */
class BehaviorTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    public function testAttachAndAccessing()
    {
        $bar = new BarClass();
        $behavior = new BarBehavior();
        $bar->attachBehavior('bar', $behavior);
        $this->assertEquals('behavior property', $bar->behaviorProperty);
        $this->assertEquals('behavior method', $bar->behaviorMethod());
        $this->assertEquals('behavior property', $bar->getBehavior('bar')->behaviorProperty);
        $this->assertEquals('behavior method', $bar->getBehavior('bar')->behaviorMethod());

        $behavior = new BarBehavior(['behaviorProperty' => 'reattached']);
        $bar->attachBehavior('bar', $behavior);
        $this->assertEquals('reattached', $bar->behaviorProperty);
    }

    public function testAutomaticAttach()
    {
        $foo = new FooClass();
        $this->assertEquals('behavior property', $foo->behaviorProperty);
        $this->assertEquals('behavior method', $foo->behaviorMethod());
    }

    public function testMagicMethods()
    {
        $bar = new BarClass();
        $behavior = new BarBehavior();

        $this->assertFalse($bar->hasMethod('magicBehaviorMethod'));
        $bar->attachBehavior('bar', $behavior);
        $this->assertFalse($bar->hasMethod('magicBehaviorMethod', false));
        $this->assertTrue($bar->hasMethod('magicBehaviorMethod'));

        $this->assertEquals('Magic Behavior Method Result!', $bar->magicBehaviorMethod());
    }

    public function testCallUnknownMethod()
    {
        $bar = new BarClass();
        $behavior = new BarBehavior();
        $this->setExpectedException('Leaps\Base\UnknownMethodException');

        $this->assertFalse($bar->hasMethod('nomagicBehaviorMethod'));
        $bar->attachBehavior('bar', $behavior);
        $bar->nomagicBehaviorMethod();
    }
}
