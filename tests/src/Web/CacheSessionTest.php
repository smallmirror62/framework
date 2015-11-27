<?php

namespace leapsunit\src\Web;

use Leaps;
use Leaps\Cache\FileCache;
use Leaps\Web\CacheSession;

/**
 * @group web
 */
class CacheSessionTest extends \leapsunit\TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
        Leaps::$app->set('cache', new FileCache());
    }

    public function testCacheSession()
    {
        $session = new CacheSession();

        $session->writeSession('test', 'sessionData');
        $this->assertEquals('sessionData', $session->readSession('test'));
        $session->destroySession('test');
        $this->assertEquals('', $session->readSession('test'));
    }

    public function testInvalidCache()
    {
        $this->setExpectedException('\Exception');
        new CacheSession(['cache' => 'invalid']);
    }
}
