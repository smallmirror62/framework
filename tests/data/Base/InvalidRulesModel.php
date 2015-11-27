<?php
namespace leapsunit\data\Base;

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
