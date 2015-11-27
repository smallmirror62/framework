<?php

namespace leapsunit\data\Validator\Model;

use leapsunit\data\Ar\ActiveRecord;

class ValidatorTestRefModel extends ActiveRecord
{

    public $test_val = 2;
    public $test_val_fail = 99;

    public static function tableName()
    {
        return 'validator_ref';
    }

    public function getMain()
    {
        return $this->hasOne(ValidatorTestMainModel::className(), ['id' => 'ref']);
    }
}
