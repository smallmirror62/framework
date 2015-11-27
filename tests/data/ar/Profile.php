<?php
/**
 * @author Carsten Brandt <mail@cebe.cc>
 */

namespace leapsunit\data\ar;

/**
 * Class Profile
 *
 * @property integer $id
 * @property string $description
 *
 */
class Profile extends ActiveRecord
{
    public static function tableName()
    {
        return 'profile';
    }
}
