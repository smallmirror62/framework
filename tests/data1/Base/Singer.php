<?php
namespace leapsunit\data\base;

use Leaps\Base\Model;

/**
 * Singer
 */
class Singer extends Model
{
    public $firstName;
    public $lastName;
    public $test;

    public function rules()
    {
        return [
            [['lastName'], 'default', 'value' => 'Lennon'],
            [['lastName'], 'required'],
            [['underscore_style'], 'Leaps\Captcha\CaptchaValidator'],
            [['test'], 'required', 'when' => function($model) { return $model->firstName === 'cebe'; }],
        ];
    }
}
