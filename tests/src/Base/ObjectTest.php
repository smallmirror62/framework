<?php
namespace leapsunit\src\Base;

use Leaps\Base\Object;
use leapsunit\TestCase;

/**
 * @group base
 */
class ObjectTest extends TestCase
{
    /**
     * @var NewObject
     */
    protected $object;

    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
        $this->object = new NewObject;
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->object = null;
    }

    public function testHasProperty()
    {
        $this->assertTrue($this->object->hasProperty('Text'));
        $this->assertTrue($this->object->hasProperty('text'));
        $this->assertFalse($this->object->hasProperty('Caption'));
        $this->assertTrue($this->object->hasProperty('content'));
        $this->assertFalse($this->object->hasProperty('content', false));
        $this->assertFalse($this->object->hasProperty('Content'));
    }

    public function testCanGetProperty()
    {
        $this->assertTrue($this->object->canGetProperty('Text'));
        $this->assertTrue($this->object->canGetProperty('text'));
        $this->assertFalse($this->object->canGetProperty('Caption'));
        $this->assertTrue($this->object->canGetProperty('content'));
        $this->assertFalse($this->object->canGetProperty('content', false));
        $this->assertFalse($this->object->canGetProperty('Content'));
    }

    public function testCanSetProperty()
    {
        $this->assertTrue($this->object->canSetProperty('Text'));
        $this->assertTrue($this->object->canSetProperty('text'));
        $this->assertFalse($this->object->canSetProperty('Object'));
        $this->assertFalse($this->object->canSetProperty('Caption'));
        $this->assertTrue($this->object->canSetProperty('content'));
        $this->assertFalse($this->object->canSetProperty('content', false));
        $this->assertFalse($this->object->canSetProperty('Content'));
    }

    public function testGetProperty()
    {
        $this->assertTrue('default' === $this->object->Text);
        $this->setExpectedException('Leaps\Base\UnknownPropertyException');
        $value2 = $this->object->Caption;
    }

    public function testSetProperty()
    {
        $value = 'new value';
        $this->object->Text = $value;
        $this->assertEquals($value, $this->object->Text);
        $this->setExpectedException('Leaps\Base\UnknownPropertyException');
        $this->object->NewMember = $value;
    }

    public function testSetReadOnlyProperty()
    {
        $this->setExpectedException('Leaps\Base\InvalidCallException');
        $this->object->object = 'test';
    }

    public function testIsset()
    {
        $this->assertTrue(isset($this->object->Text));
        $this->assertFalse(empty($this->object->Text));

        $this->object->Text = '';
        $this->assertTrue(isset($this->object->Text));
        $this->assertTrue(empty($this->object->Text));

        $this->object->Text = null;
        $this->assertFalse(isset($this->object->Text));
        $this->assertTrue(empty($this->object->Text));

        $this->assertFalse(isset($this->object->unknownProperty));
        $this->assertTrue(empty($this->object->unknownProperty));
    }

    public function testUnset()
    {
        unset($this->object->Text);
        $this->assertFalse(isset($this->object->Text));
        $this->assertTrue(empty($this->object->Text));
    }

    public function testUnsetReadOnlyProperty()
    {
        $this->setExpectedException('Leaps\Base\InvalidCallException');
        unset($this->object->object);
    }

    public function testCallUnknownMethod()
    {
        $this->setExpectedException('Leaps\Base\UnknownMethodException');
        $this->object->unknownMethod();
    }

    public function testArrayProperty()
    {
        $this->assertEquals([], $this->object->items);
        // the following won't work
        /*
        $this->object->items[] = 1;
        $this->assertEquals([1], $this->object->items);
        */
    }

    public function testObjectProperty()
    {
        $this->assertTrue($this->object->object instanceof NewObject);
        $this->assertEquals('object text', $this->object->object->text);
        $this->object->object->text = 'new text';
        $this->assertEquals('new text', $this->object->object->text);
    }

    public function testConstruct()
    {
        $object = new NewObject(['text' => 'test text']);
        $this->assertEquals('test text', $object->getText());
    }

    public function testGetClassName()
    {
        $object = $this->object;
        $this->assertSame(get_class($object), $object::className());
    }

    public function testReadingWriteOnlyProperty()
    {
        $this->setExpectedException(
            'Leaps\Base\InvalidCallException',
            'Getting write-only property: leapsunit\src\base\NewObject::writeOnly'
        );
        $this->object->writeOnly;
    }
}


class NewObject extends Object
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

    public function setWriteOnly() {}
}
