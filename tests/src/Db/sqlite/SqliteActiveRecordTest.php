<?php
namespace leapsunit\src\db\sqlite;

use leapsunit\src\db\ActiveRecordTest;

/**
 * @group db
 * @group sqlite
 */
class SqliteActiveRecordTest extends ActiveRecordTest
{
    protected $driverName = 'sqlite';
}
