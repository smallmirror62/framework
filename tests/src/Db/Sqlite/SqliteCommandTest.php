<?php
namespace leapsunit\src\Db\Sqlite;

use leapsunit\src\Db\CommandTest;

/**
 * @group db
 * @group sqlite
 */
class SqliteCommandTest extends CommandTest
{
    protected $driverName = 'sqlite';

    public function testAutoQuoting()
    {
        $db = $this->getConnection(false);

        $sql = 'SELECT [[id]], [[t.name]] FROM {{customer}} t';
        $command = $db->createCommand($sql);
        $this->assertEquals("SELECT `id`, `t`.`name` FROM `customer` t", $command->sql);
    }
}
