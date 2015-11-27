<?php
namespace leapsunit\src\db\mssql;

use leapsunit\src\data\ActiveDataProviderTest;

/**
 * @group db
 * @group mssql
 * @group data
 */
class MssqlActiveDataProviderTest extends ActiveDataProviderTest
{
    public $driverName = 'sqlsrv';
}
