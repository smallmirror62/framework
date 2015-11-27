<?php

namespace leapsunit\src\Console\Controller;

use Leaps;
use leapsunit\TestCase;
use Leaps\Console\Controller\CacheController;

/**
 * Unit test for [[\Leaps\Console\Controller\CacheController]].
 * @see CacheController
 *
 * @group console
 */
class CacheControllerTest extends TestCase
{

    /**
     * @var SilencedCacheController
     */
    private $_cacheController;

    private $driverName = 'mysql';

    protected function setUp()
    {
        parent::setUp();

        $this->_cacheController = Leaps::createObject([
            'class' => 'leapsunit\src\Console\Controller\SilencedCacheController',
            'interactive' => false,
        ],[null, null]); //id and module are null

        $databases = self::getParam('databases');
        $config = $databases[$this->driverName];
        $pdoDriver = 'pdo_' . $this->driverName;

        if (!extension_loaded('pdo') || !extension_loaded($pdoDriver)) {
            $this->markTestSkipped('pdo and ' . $pdoDriver . ' extensions are required.');
        }


        $this->mockApplication([
            'components' => [
                'firstCache' => 'Leaps\Cache\ArrayCache',
                'secondCache' => 'Leaps\Cache\ArrayCache',
                'session' => 'Leaps\Web\CacheSession', // should be ignored at `actionFlushAll()`
                'db' => [
                    'class' => isset($config['class']) ? $config['class'] : 'Leaps\Db\Connection',
                    'dsn' => $config['dsn'],
                    'username' => isset($config['username']) ? $config['username'] : null,
                    'password' => isset($config['password']) ? $config['password'] : null,
                    'enableSchemaCache' => true,
                    'schemaCache' => 'firstCache',
                ],
            ],
        ]);

        if(isset($config['fixture'])) {
            Leaps::$app->db->open();
            $lines = explode(';', file_get_contents($config['fixture']));
            foreach ($lines as $line) {
                if (trim($line) !== '') {
                    Leaps::$app->db->pdo->exec($line);
                }
            }
        }
    }

    public function testFlushOne()
    {
        Leaps::$app->firstCache->set('firstKey', 'firstValue');
        Leaps::$app->firstCache->set('secondKey', 'secondValue');
        Leaps::$app->secondCache->set('thirdKey', 'thirdValue');

        $this->_cacheController->actionFlush('firstCache');

        $this->assertFalse(Leaps::$app->firstCache->get('firstKey'),'first cache data should be flushed');
        $this->assertFalse(Leaps::$app->firstCache->get('secondKey'),'first cache data should be flushed');
        $this->assertEquals('thirdValue', Leaps::$app->secondCache->get('thirdKey'), 'second cache data should not be flushed');
    }

    public function testClearSchema()
    {
        $schema = Leaps::$app->db->schema;
        Leaps::$app->db->createCommand()->createTable('test_schema_cache', ['id' => 'pk'])->execute();
        $noCacheSchemas = $schema->getTableSchemas('', true);
        $cacheSchema = $schema->getTableSchemas('', false);

        $this->assertEquals($noCacheSchemas, $cacheSchema, 'Schema should not be modified.');

        Leaps::$app->db->createCommand()->dropTable('test_schema_cache')->execute();
        $noCacheSchemas = $schema->getTableSchemas('', true);
        $this->assertNotEquals($noCacheSchemas, $cacheSchema, 'Schemas should be different.');

        $this->_cacheController->actionFlushSchema('db');
        $cacheSchema = $schema->getTableSchemas('', false);
        $this->assertEquals($noCacheSchemas, $cacheSchema, 'Schema cache should be flushed.');

    }

    public function testFlushBoth()
    {
        Leaps::$app->firstCache->set('firstKey', 'firstValue');
        Leaps::$app->firstCache->set('secondKey', 'secondValue');
        Leaps::$app->secondCache->set('thirdKey', 'secondValue');

        $this->_cacheController->actionFlush('firstCache', 'secondCache');

        $this->assertFalse(Leaps::$app->firstCache->get('firstKey'),'first cache data should be flushed');
        $this->assertFalse(Leaps::$app->firstCache->get('secondKey'),'first cache data should be flushed');
        $this->assertFalse(Leaps::$app->secondCache->get('thirdKey'), 'second cache data should be flushed');
    }

    public function testNotFoundFlush()
    {
        Leaps::$app->firstCache->set('firstKey', 'firstValue');

        $this->_cacheController->actionFlush('notExistingCache');

        $this->assertEquals('firstValue', Leaps::$app->firstCache->get('firstKey'), 'first cache data should not be flushed');
    }

    /**
     * @expectedException \Leaps\Console\Exception
     */
    public function testNothingToFlushException()
    {
        $this->_cacheController->actionFlush();
    }

    public function testFlushAll()
    {
        Leaps::$app->firstCache->set('firstKey', 'firstValue');
        Leaps::$app->secondCache->set('thirdKey', 'secondValue');

        $this->_cacheController->actionFlushAll();

        $this->assertFalse(Leaps::$app->firstCache->get('firstKey'),'first cache data should be flushed');
        $this->assertFalse(Leaps::$app->secondCache->get('thirdKey'), 'second cache data should be flushed');
    }

}
