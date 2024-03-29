<?php
namespace leapsunit\data\Base;

use Leaps\Base\Model;

/**
 * model to test different rules combinations in ModelTest
 */
class RulesModel extends Model
{
    public $account_id;
    public $user_id;
    public $email;
    public $name;

    public $rules = [];

    public function rules()
    {
        return $this->rules;
    }
}
