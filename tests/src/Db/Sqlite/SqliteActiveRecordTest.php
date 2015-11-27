<?php
namespace leapsunit\src\Db\Sqlite;

use leapsunit\src\Db\ActiveRecordTest;

/**
 * @group db
 * @group sqlite
 */
class SqliteActiveRecordTest extends ActiveRecordTest
{
    protected $driverName = 'sqlite';
}
