<?php
namespace leapsunit\src\Db\Sqlite;

use leapsunit\src\Data\ActiveDataProviderTest;

/**
 * @group db
 * @group sqlite
 * @group data
 */
class SqliteActiveDataProviderTest extends ActiveDataProviderTest
{
    public $driverName = 'sqlite';
}
