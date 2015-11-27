<?php
namespace leapsunit\src\Db\Cubrid;

use leapsunit\src\Data\ActiveDataProviderTest;

/**
 * @group db
 * @group cubrid
 * @group data
 */
class CubridActiveDataProviderTest extends ActiveDataProviderTest
{
    public $driverName = 'cubrid';
}
