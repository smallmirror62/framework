<?php

namespace leapsunit\data\Validator\Model;

use leapsunit\data\Ar\ActiveRecord;

class ValidatorTestMainModel extends ActiveRecord
{
    public $testMainVal = 1;

    public static function tableName()
    {
        return 'validator_main';
    }

    public function getReferences()
    {
        return $this->hasMany(ValidatorTestRefModel::className(), ['ref' => 'id']);
    }
}
