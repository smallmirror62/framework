<?php

namespace leapsunit\src\db\mssql;

use leapsunit\src\db\QueryTest;

/**
 * @group db
 * @group mssql
 */
class MssqlQueryTest extends QueryTest
{
    protected $driverName = 'sqlsrv';
}
