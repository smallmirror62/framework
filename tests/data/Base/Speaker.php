<?php
namespace leapsunit\data\Base;

use Leaps\Base\Model;

/**
 * Speaker
 */
class Speaker extends Model
{
    public $firstName;
    public $lastName;

    public $customLabel;
    public $underscore_style;

    protected $protectedProperty;
    private $_privateProperty;

    public static $formName = 'Speaker';

    public function formName()
    {
        return static::$formName;
    }

    public function attributeLabels()
    {
        return [
            'customLabel' => 'This is the custom label',
        ];
    }

    public function rules()
    {
        return [];
    }

    public function scenarios()
    {
        return [
            'test' => ['firstName', 'lastName', '!underscore_style'],
        ];
    }
}
