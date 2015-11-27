<?php

namespace leapsunit\src\db\mssql;

use leapsunit\src\db\ActiveRecordTest;

/**
 * @group db
 * @group mssql
 */
class MssqlActiveRecordTest extends ActiveRecordTest
{
    protected $driverName = 'sqlsrv';
}
