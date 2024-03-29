<?php
namespace leapsunit\src\Cache;

use Leaps\Cache\XCache;

/**
 * Class for testing xcache backend
 * @group xcache
 * @group caching
 */
class XCacheTest extends CacheTestCase
{
    private $_cacheInstance = null;

    /**
     * @return XCache
     */
    protected function getCacheInstance()
    {
        if (!function_exists("xcache_isset")) {
            $this->markTestSkipped("XCache not installed. Skipping.");
        }

        if ($this->_cacheInstance === null) {
            $this->_cacheInstance = new XCache();
        }

        return $this->_cacheInstance;
    }
}
