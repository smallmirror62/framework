<?php
namespace leapsunit\src\Db\Oci;

use leapsunit\src\Data\ActiveDataProviderTest;

/**
 * @group db
 * @group oci
 * @group data
 */
class OracleActiveDataProviderTest extends ActiveDataProviderTest
{
    public $driverName = 'oci';
}
