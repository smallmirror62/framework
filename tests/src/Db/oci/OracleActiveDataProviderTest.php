<?php
namespace leapsunit\src\db\oci;

use leapsunit\src\data\ActiveDataProviderTest;
use yii\data\ActiveDataProvider;
use leapsunit\data\ar\Order;

/**
 * @group db
 * @group oci
 * @group data
 */
class OracleActiveDataProviderTest extends ActiveDataProviderTest
{
    public $driverName = 'oci';
}
