<?php
namespace leapsunit\src\Db\Pgsql;

use leapsunit\src\Data\ActiveDataProviderTest;

/**
 * @group db
 * @group pgsql
 * @group data
 */
class PostgreSQLActiveDataProviderTest extends ActiveDataProviderTest
{
    public $driverName = 'pgsql';
}
