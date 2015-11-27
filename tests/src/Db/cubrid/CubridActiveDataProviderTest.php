<?php
namespace leapsunit\src\db\cubrid;

use leapsunit\src\data\ActiveDataProviderTest;

/**
 * @group db
 * @group cubrid
 * @group data
 */
class CubridActiveDataProviderTest extends ActiveDataProviderTest
{
    public $driverName = 'cubrid';
}
