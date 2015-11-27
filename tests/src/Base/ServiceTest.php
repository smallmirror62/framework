<?php
namespace leapsunit\src\Base;
use Leaps\Base\Event;
use Leaps\Base\Behavior;
use Leaps\Base\Service;

use leapsunit\TestCase;

function globalEventHandler($event)
{
    $event->sender->eventHandled = true;
}

function globalEventHandler2($event)
{
    $event->sender->eventHandled = true;
    $event->handled = true;
}

/**
 * @group base
 */
class ServiceTest extends TestCase
{
    /**
     * @var NewService
     */
    protected $service;

    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
        $this->service = new NewService();
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->service = null;
    }

    public function testClone()
    {
        $component = new NewService();
        $behavior = new NewBehavior();
        $component->attachBehavior('a', $behavior);
        $this->assertSame($behavior, $component->getBehavior('a'));
        $component->on('test', 'fake');
        $this->assertTrue($component->hasEventHandlers('test'));

        $clone = clone $component;
        $this->assertNotSame($component, $clone);
        $this->assertNull($clone->getBehavior('a'));
        $this->assertFalse($clone->hasEventHandlers('test'));
    }

    public function testHasProperty()
    {
        $this->assertTrue($this->service->hasProperty('Text'));
        $this->assertTrue($this->service->hasProperty('text'));
        $this->assertFalse($this->service->hasProperty('Caption'));
        $this->assertTrue($this->service->hasProperty('content'));
        $this->assertFalse($this->service->hasProperty('content', false));
        $this->assertFalse($this->service->hasProperty('Content'));
    }

    public function testCanGetProperty()
    {
        $this->assertTrue($this->service->canGetProperty('Text'));
        $this->assertTrue($this->service->canGetProperty('text'));
        $this->assertFalse($this->service->canGetProperty('Caption'));
        $this->assertTrue($this->service->canGetProperty('content'));
        $this->assertFalse($this->service->canGetProperty('content', false));
        $this->assertFalse($this->service->canGetProperty('Content'));
    }

    public function testCanSetProperty()
    {
        $this->assertTrue($this->service->canSetProperty('Text'));
        $this->assertTrue($this->service->canSetProperty('text'));
        $this->assertFalse($this->service->canSetProperty('Object'));
        $this->assertFalse($this->service->canSetProperty('Caption'));
        $this->assertTrue($this->service->canSetProperty('content'));
        $this->assertFalse($this->service->canSetProperty('content', false));
        $this->assertFalse($this->service->canSetProperty('Content'));

        // behavior
        $this->assertFalse($this->service->canSetProperty('p2'));
        $behavior = new NewBehavior();
        $this->service->attachBehavior('a', $behavior);
        $this->assertTrue($this->service->canSetProperty('p2'));
        $this->service->detachBehavior('a');
    }

    public function testGetProperty()
    {
        $this->assertTrue('default' === $this->service->Text);
        $this->setExpectedException('Leaps\Base\UnknownPropertyException');
        $value2 = $this->service->Caption;
    }

    public function testSetProperty()
    {
        $value = 'new value';
        $this->service->Text = $value;
        $this->assertEquals($value, $this->service->Text);
        $this->setExpectedException('Leaps\Base\UnknownPropertyException');
        $this->service->NewMember = $value;
    }

    public function testIsset()
    {
        $this->assertTrue(isset($this->service->Text));
        $this->assertFalse(empty($this->service->Text));

        $this->service->Text = '';
        $this->assertTrue(isset($this->service->Text));
        $this->assertTrue(empty($this->service->Text));

        $this->service->Text = null;
        $this->assertFalse(isset($this->service->Text));
        $this->assertTrue(empty($this->service->Text));

        $this->assertFalse(isset($this->service->p2));
        $this->service->attachBehavior('a', new NewBehavior());
        $this->service->setP2('test');
        $this->assertTrue(isset($this->service->p2));
    }

    public function testCallUnknownMethod()
    {
        $this->setExpectedException('Leaps\Base\UnknownMethodException');
        $this->service->unknownMethod();
    }

    public function testUnset()
    {
        unset($this->service->Text);
        $this->assertFalse(isset($this->service->Text));
        $this->assertTrue(empty($this->service->Text));

        $this->service->attachBehavior('a', new NewBehavior());
        $this->service->setP2('test');
        $this->assertEquals('test', $this->service->getP2());

        unset($this->service->p2);
        $this->assertNull($this->service->getP2());
    }

    public function testUnsetReadonly()
    {
        $this->setExpectedException('Leaps\Base\InvalidCallException');
        unset($this->service->object);
    }

    public function testOn()
    {
        $this->assertFalse($this->service->hasEventHandlers('click'));
        $this->service->on('click', 'foo');
        $this->assertTrue($this->service->hasEventHandlers('click'));

        $this->assertFalse($this->service->hasEventHandlers('click2'));
        $p = 'on click2';
        $this->service->$p = 'foo2';
        $this->assertTrue($this->service->hasEventHandlers('click2'));
    }

    public function testOff()
    {
        $this->assertFalse($this->service->hasEventHandlers('click'));
        $this->service->on('click', 'foo');
        $this->assertTrue($this->service->hasEventHandlers('click'));
        $this->service->off('click', 'foo');
        $this->assertFalse($this->service->hasEventHandlers('click'));

        $this->service->on('click2', 'foo');
        $this->service->on('click2', 'foo2');
        $this->service->on('click2', 'foo3');
        $this->assertTrue($this->service->hasEventHandlers('click2'));
        $this->service->off('click2', 'foo3');
        $this->assertTrue($this->service->hasEventHandlers('click2'));
        $this->service->off('click2');
        $this->assertFalse($this->service->hasEventHandlers('click2'));
    }

    public function testTrigger()
    {
        $this->service->on('click', [$this->service, 'myEventHandler']);
        $this->assertFalse($this->service->eventHandled);
        $this->assertNull($this->service->event);
        $this->service->raiseEvent();
        $this->assertTrue($this->service->eventHandled);
        $this->assertEquals('click', $this->service->event->name);
        $this->assertEquals($this->service, $this->service->event->sender);
        $this->assertFalse($this->service->event->handled);

        $eventRaised = false;
        $this->service->on('click', function ($event) use (&$eventRaised) {
            $eventRaised = true;
        });
        $this->service->raiseEvent();
        $this->assertTrue($eventRaised);

        // raise event w/o parameters
        $eventRaised = false;
        $this->service->on('test', function ($event) use (&$eventRaised) {
            $eventRaised = true;
        });
        $this->service->trigger('test');
        $this->assertTrue($eventRaised);
    }

    public function testHasEventHandlers()
    {
        $this->assertFalse($this->service->hasEventHandlers('click'));
        $this->service->on('click', 'foo');
        $this->assertTrue($this->service->hasEventHandlers('click'));
    }

    public function testStopEvent()
    {
        $component = new NewService;
        $component->on('click', 'leapsunit\src\base\globalEventHandler2');
        $component->on('click', [$this->service, 'myEventHandler']);
        $component->raiseEvent();
        $this->assertTrue($component->eventHandled);
        $this->assertFalse($this->service->eventHandled);
    }

    public function testAttachBehavior()
    {
        $component = new NewService;
        $this->assertFalse($component->hasProperty('p'));
        $this->assertFalse($component->behaviorCalled);
        $this->assertNull($component->getBehavior('a'));

        $behavior = new NewBehavior;
        $component->attachBehavior('a', $behavior);
        $this->assertSame($behavior, $component->getBehavior('a'));
        $this->assertTrue($component->hasProperty('p'));
        $component->test();
        $this->assertTrue($component->behaviorCalled);

        $this->assertSame($behavior, $component->detachBehavior('a'));
        $this->assertFalse($component->hasProperty('p'));
        $this->setExpectedException('Leaps\Base\UnknownMethodException');
        $component->test();

        $p = 'as b';
        $component = new NewService;
        $component->$p = ['class' => 'NewBehavior'];
        $this->assertSame($behavior, $component->getBehavior('a'));
        $this->assertTrue($component->hasProperty('p'));
        $component->test();
        $this->assertTrue($component->behaviorCalled);
    }

    public function testAttachBehaviors()
    {
        $component = new NewService;
        $this->assertNull($component->getBehavior('a'));
        $this->assertNull($component->getBehavior('b'));

        $behavior = new NewBehavior;

        $component->attachBehaviors([
            'a' => $behavior,
            'b' => $behavior,
        ]);

        $this->assertSame(['a' => $behavior, 'b' => $behavior], $component->getBehaviors());
    }

    public function testDetachBehavior()
    {
        $component = new NewService;
        $behavior = new NewBehavior;

        $component->attachBehavior('a', $behavior);
        $this->assertSame($behavior, $component->getBehavior('a'));

        $detachedBehavior = $component->detachBehavior('a');
        $this->assertSame($detachedBehavior, $behavior);
        $this->assertNull($component->getBehavior('a'));

        $detachedBehavior = $component->detachBehavior('z');
        $this->assertNull($detachedBehavior);
    }

    public function testDetachBehaviors()
    {
        $component = new NewService;
        $behavior = new NewBehavior;

        $component->attachBehavior('a', $behavior);
        $this->assertSame($behavior, $component->getBehavior('a'));
        $component->attachBehavior('b', $behavior);
        $this->assertSame($behavior, $component->getBehavior('b'));

        $component->detachBehaviors();
        $this->assertNull($component->getBehavior('a'));
        $this->assertNull($component->getBehavior('b'));
    }

    public function testSetReadOnlyProperty()
    {
        $this->setExpectedException(
            '\Leaps\Base\InvalidCallException',
            'Setting read-only property: leapsunit\src\base\NewService::object'
        );
        $this->service->object = 'z';
    }

    public function testSetPropertyOfBehavior()
    {
        $this->assertNull($this->service->getBehavior('a'));

        $behavior = new NewBehavior;
        $this->service->attachBehaviors([
            'a' => $behavior,
        ]);
        $this->service->p = 'Leaps is cool.';

        $this->assertSame('Leaps is cool.', $this->service->getBehavior('a')->p);
    }

    public function testSettingBehaviorWithSetter()
    {
        $behaviorName = 'foo';
        $this->assertNull($this->service->getBehavior($behaviorName));
        $p = 'as ' . $behaviorName;
        $this->service->$p = __NAMESPACE__ .  '\NewBehavior';
        $this->assertSame(__NAMESPACE__ .  '\NewBehavior', get_class($this->service->getBehavior($behaviorName)));
    }

    public function testWriteOnlyProperty()
    {
        $this->setExpectedException(
            '\Leaps\Base\InvalidCallException',
            'Getting write-only property: leapsunit\src\base\NewService::writeOnly'
        );
        $this->service->writeOnly;
    }

    public function testSuccessfulMethodCheck()
    {
        $this->assertTrue($this->service->hasMethod('hasProperty'));
    }

    public function testTurningOffNonExistingBehavior()
    {
        $this->assertFalse($this->service->hasEventHandlers('foo'));
        $this->assertFalse($this->service->off('foo'));
    }
}

class NewService extends Service
{
    private $_object = null;
    private $_text = 'default';
    private $_items = [];
    public $content;

    public function getText()
    {
        return $this->_text;
    }

    public function setText($value)
    {
        $this->_text = $value;
    }

    public function getObject()
    {
        if (!$this->_object) {
            $this->_object = new self;
            $this->_object->_text = 'object text';
        }

        return $this->_object;
    }

    public function getExecute()
    {
        return function ($param) {
            return $param * 2;
        };
    }

    public function getItems()
    {
        return $this->_items;
    }

    public $eventHandled = false;
    public $event;
    public $behaviorCalled = false;

    public function myEventHandler($event)
    {
        $this->eventHandled = true;
        $this->event = $event;
    }

    public function raiseEvent()
    {
        $this->trigger('click', new Event);
    }

    public function setWriteOnly()
    {
    }
}

class NewBehavior extends Behavior
{
    public $p;
    private $p2;

    public function getP2()
    {
        return $this->p2;
    }

    public function setP2($value)
    {
        $this->p2 = $value;
    }

    public function test()
    {
        $this->owner->behaviorCalled = true;

        return 2;
    }
}

class NewService2 extends Service
{
    public $a;
    public $b;
    public $c;

    public function __construct($b, $c)
    {
        $this->b = $b;
        $this->c = $c;
    }
}
