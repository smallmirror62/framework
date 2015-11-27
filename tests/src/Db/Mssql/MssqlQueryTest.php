<?php

namespace leapsunit\src\Db\mssql;

use leapsunit\src\Db\QueryTest;

/**
 * @group db
 * @group mssql
 */
class MssqlQueryTest extends QueryTest
{
    protected $driverName = 'sqlsrv';
}
