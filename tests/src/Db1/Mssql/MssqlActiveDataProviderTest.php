<?php
namespace leapsunit\src\Db\mssql;

use leapsunit\src\Data\ActiveDataProviderTest;

/**
 * @group db
 * @group mssql
 * @group data
 */
class MssqlActiveDataProviderTest extends ActiveDataProviderTest
{
    public $driverName = 'sqlsrv';
}
