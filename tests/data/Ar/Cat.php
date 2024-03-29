<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Leaps Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace leapsunit\data\Ar;

/**
 * Class Cat
 *
 * @author Jose Lorente <jose.lorente.martin@gmail.com>
 * @since 2.0
 */
class Cat extends Animal
{

    /**
     * 
     * @param self $record
     * @param array $row
     */
    public static function populateRecord($record, $row)
    {
        parent::populateRecord($record, $row);

        $record->does = 'meow';
    }

}
