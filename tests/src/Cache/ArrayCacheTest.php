<?php

namespace leapsunit\src\Cache;

use Leaps\Cache\ArrayCache;

/**
 * Class for testing file cache backend
 * @group caching
 */
class ArrayCacheTest extends CacheTestCase
{
    private $_cacheInstance = null;

    /**
     * @return ArrayCache
     */
    protected function getCacheInstance()
    {
        if ($this->_cacheInstance === null) {
            $this->_cacheInstance = new ArrayCache();
        }
        return $this->_cacheInstance;
    }

    public function testExpire()
    {
        $cache = $this->getCacheInstance();

        static::$microtime = \microtime(true);
        $this->assertTrue($cache->set('expire_test', 'expire_test', 2));
        static::$microtime++;
        $this->assertEquals('expire_test', $cache->get('expire_test'));
        static::$microtime++;
        $this->assertFalse($cache->get('expire_test'));
    }

    public function testExpireAdd()
    {
        $cache = $this->getCacheInstance();

        static::$microtime = \microtime(true);
        $this->assertTrue($cache->add('expire_testa', 'expire_testa', 2));
        static::$microtime++;
        $this->assertEquals('expire_testa', $cache->get('expire_testa'));
        static::$microtime++;
        $this->assertFalse($cache->get('expire_testa'));
    }
}
