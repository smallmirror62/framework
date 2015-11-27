<?php
namespace leapsunit\src\db\sqlite;

use leapsunit\src\data\ActiveDataProviderTest;

/**
 * @group db
 * @group sqlite
 * @group data
 */
class SqliteActiveDataProviderTest extends ActiveDataProviderTest
{
    public $driverName = 'sqlite';
}
