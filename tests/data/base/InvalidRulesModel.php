<?php
namespace leapsunit\data\base;

use Leaps\Base\Model;

/**
 * InvalidRulesModel
 */
class InvalidRulesModel extends Model
{
    public function rules()
    {
        return [
            ['test'],
        ];
    }
}
