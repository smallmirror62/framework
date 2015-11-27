<?php

namespace leapsunit\src\Db\mssql;

use leapsunit\src\Db\ActiveRecordTest;

/**
 * @group db
 * @group mssql
 */
class MssqlActiveRecordTest extends ActiveRecordTest
{
    protected $driverName = 'sqlsrv';
}
