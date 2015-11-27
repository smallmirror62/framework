<?php
namespace leapsunit\src\Rbac;

use Leaps\Cache\FileCache;
use Leaps\Rbac\DbManager;

/**
 * MySQLManagerCacheTest
 * @group db
 * @group rbac
 */
class MySQLManagerCacheTest extends MySQLManagerTest
{
    /**
     * @return \Leaps\Rbac\ManagerInterface
     */
    protected function createManager()
    {
        return new DbManager([
            'db' => $this->getConnection(),
            'cache' => new FileCache(['cachePath' => '@leapsunit/runtime/cache']),
        ]);
    }
}
