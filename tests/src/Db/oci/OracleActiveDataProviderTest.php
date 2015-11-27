<?php
namespace leapsunit\src\db\oci;

use leapsunit\src\data\ActiveDataProviderTest;
use Leaps\Data\ActiveDataProvider;
use leapsunit\data\Ar\Order;

/**
 * @group db
 * @group oci
 * @group data
 */
class OracleActiveDataProviderTest extends ActiveDataProviderTest
{
    public $driverName = 'oci';
}
