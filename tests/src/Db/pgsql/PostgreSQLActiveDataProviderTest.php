<?php
namespace leapsunit\src\db\pgsql;

use leapsunit\src\data\ActiveDataProviderTest;

/**
 * @group db
 * @group pgsql
 * @group data
 */
class PostgreSQLActiveDataProviderTest extends ActiveDataProviderTest
{
    public $driverName = 'pgsql';
}
