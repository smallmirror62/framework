<?php

namespace leapsunit\src\Validator;

use Leaps;
use Leaps\Base\Exception;
use Leaps\Validator\ExistValidator;
use leapsunit\data\Ar\ActiveRecord;
use leapsunit\data\Ar\Order;
use leapsunit\data\Ar\OrderItem;
use leapsunit\data\Validator\Model\ValidatorTestMainModel;
use leapsunit\data\Validator\Model\ValidatorTestRefModel;
use leapsunit\src\Db\DatabaseTestCase;

/**
 * @group validators
 */
class ExistValidatorTest extends DatabaseTestCase
{
    protected $driverName = 'mysql';

    public function setUp()
    {
        parent::setUp();
        $this->mockApplication();
        ActiveRecord::$db = $this->getConnection();
    }

    public function testValidateValueExpectedException()
    {
        try {
            $val = new ExistValidator();
            $val->validate('ref');
            $this->fail('Exception should have been thrown at this time');
        } catch (Exception $e) {
            $this->assertInstanceOf('Leaps\Base\InvalidConfigException', $e);
            $this->assertEquals('The "targetClass" property must be set.', $e->getMessage());
        }
        // combine to save the time creating a new db-fixture set (likely ~5 sec)
        try {
            $val = new ExistValidator(['targetClass' => ValidatorTestMainModel::className()]);
            $val->validate('ref');
            $this->fail('Exception should have been thrown at this time');
        } catch (Exception $e) {
            $this->assertInstanceOf('Leaps\Base\InvalidConfigException', $e);
            $this->assertEquals('The "targetAttribute" property must be configured as a string.', $e->getMessage());
        }
    }

    public function testValidateValue()
    {
        $val = new ExistValidator(['targetClass' => ValidatorTestRefModel::className(), 'targetAttribute' => 'id']);
        $this->assertTrue($val->validate(2));
        $this->assertTrue($val->validate(5));
        $this->assertFalse($val->validate(99));
        $this->assertFalse($val->validate(['1']));
    }

    public function testValidateAttribute()
    {
        // existing value on different table
        $val = new ExistValidator(['targetClass' => ValidatorTestMainModel::className(), 'targetAttribute' => 'id']);
        $m = ValidatorTestRefModel::findOne(['id' => 1]);
        $val->validateAttribute($m, 'ref');
        $this->assertFalse($m->hasErrors());
        // non-existing value on different table
        $val = new ExistValidator(['targetClass' => ValidatorTestMainModel::className(), 'targetAttribute' => 'id']);
        $m = ValidatorTestRefModel::findOne(['id' => 6]);
        $val->validateAttribute($m, 'ref');
        $this->assertTrue($m->hasErrors('ref'));
        // existing value on same table
        $val = new ExistValidator(['targetAttribute' => 'ref']);
        $m = ValidatorTestRefModel::findOne(['id' => 2]);
        $val->validateAttribute($m, 'test_val');
        $this->assertFalse($m->hasErrors());
        // non-existing value on same table
        $val = new ExistValidator(['targetAttribute' => 'ref']);
        $m = ValidatorTestRefModel::findOne(['id' => 5]);
        $val->validateAttribute($m, 'test_val_fail');
        $this->assertTrue($m->hasErrors('test_val_fail'));
        // check for given value (true)
        $val = new ExistValidator();
        $m = ValidatorTestRefModel::findOne(['id' => 3]);
        $val->validateAttribute($m, 'ref');
        $this->assertFalse($m->hasErrors());
        // check for given defaults (false)
        $val = new ExistValidator();
        $m = ValidatorTestRefModel::findOne(['id' => 4]);
        $m->a_field = 'some new value';
        $val->validateAttribute($m, 'a_field');
        $this->assertTrue($m->hasErrors('a_field'));
        // existing array
        $val = new ExistValidator(['targetAttribute' => 'ref']);
        $val->allowArray = true;
        $m = new ValidatorTestRefModel();
        $m->test_val = [2, 3, 4, 5];
        $val->validateAttribute($m, 'test_val');
        $this->assertFalse($m->hasErrors('test_val'));
        // non-existing array
        $val = new ExistValidator(['targetAttribute' => 'ref']);
        $val->allowArray = true;
        $m = new ValidatorTestRefModel();
        $m->test_val = [95, 96, 97, 98];
        $val->validateAttribute($m, 'test_val');
        $this->assertTrue($m->hasErrors('test_val'));
        // partial-existing array
        $val = new ExistValidator(['targetAttribute' => 'ref']);
        $val->allowArray = true;
        $m = new ValidatorTestRefModel();
        $m->test_val = [2, 97, 3, 98];
        $val->validateAttribute($m, 'test_val');
        $this->assertTrue($m->hasErrors('test_val'));
        // existing array (allowArray = false)
        $val = new ExistValidator(['targetAttribute' => 'ref']);
        $val->allowArray = false;
        $m = new ValidatorTestRefModel();
        $m->test_val = [2, 3, 4, 5];
        $val->validateAttribute($m, 'test_val');
        $this->assertTrue($m->hasErrors('test_val'));
        // non-existing array (allowArray = false)
        $val = new ExistValidator(['targetAttribute' => 'ref']);
        $val->allowArray = false;
        $m = new ValidatorTestRefModel();
        $m->test_val = [95, 96, 97, 98];
        $val->validateAttribute($m, 'test_val');
        $this->assertTrue($m->hasErrors('test_val'));
    }

    public function testValidateCompositeKeys()
    {
        $val = new ExistValidator([
            'targetClass' => OrderItem::className(),
            'targetAttribute' => ['order_id', 'item_id'],
        ]);
        // validate old record
        $m = OrderItem::findOne(['order_id' => 1, 'item_id' => 2]);
        $val->validateAttribute($m, 'order_id');
        $this->assertFalse($m->hasErrors('order_id'));

        // validate new record
        $m = new OrderItem(['order_id' => 1, 'item_id' => 2]);
        $val->validateAttribute($m, 'order_id');
        $this->assertFalse($m->hasErrors('order_id'));
        $m = new OrderItem(['order_id' => 10, 'item_id' => 2]);
        $val->validateAttribute($m, 'order_id');
        $this->assertTrue($m->hasErrors('order_id'));

        $val = new ExistValidator([
            'targetClass' => OrderItem::className(),
            'targetAttribute' => ['id' => 'order_id'],
        ]);
        // validate old record
        $m = Order::findOne(1);
        $val->validateAttribute($m, 'id');
        $this->assertFalse($m->hasErrors('id'));
        $m = Order::findOne(1);
        $m->id = 10;
        $val->validateAttribute($m, 'id');
        $this->assertTrue($m->hasErrors('id'));

        $m = new Order(['id' => 1]);
        $val->validateAttribute($m, 'id');
        $this->assertFalse($m->hasErrors('id'));
        $m = new Order(['id' => 10]);
        $val->validateAttribute($m, 'id');
        $this->assertTrue($m->hasErrors('id'));
    }
}
