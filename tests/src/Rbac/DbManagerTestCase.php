<?php
namespace leapsunit\src\Rbac;

use Leaps;
use Leaps\Console\Application;
use Leaps\Console\Controller;
use Leaps\Db\Connection;
use Leaps\Rbac\DbManager;
use leapsunit\src\Console\Controller\EchoMigrateController;

/**
 * DbManagerTestCase
 */
abstract class DbManagerTestCase extends ManagerTestCase
{
    protected static $database;
    protected static $driverName = 'mysql';

    /**
     * @var Connection
     */
    protected static $db;

    protected static function runConsoleAction($route, $params = [])
    {
        if (Leaps::$app === null) {
            new Application([
                'id' => 'Migrator',
                'basePath' => '@leapsunit',
                'controllerMap' => [
                    'migrate' => EchoMigrateController::className(),
                ],
                'components' => [
                    'db' => static::getConnection(),
                    'authManager' => '\Leaps\Rbac\DbManager',
                ],
            ]);
        }

        ob_start();
        $result = Leaps::$app->runAction($route, $params);
        echo "Result is " . $result;
        if ($result !== Controller::EXIT_CODE_NORMAL) {
            ob_end_flush();
        } else {
            ob_end_clean();
        }
    }

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        $databases = static::getParam('databases');
        static::$database = $databases[static::$driverName];
        $pdo_database = 'pdo_' . static::$driverName;

        if (!extension_loaded('pdo') || !extension_loaded($pdo_database)) {
            static::markTestSkipped('pdo and ' . $pdo_database . ' extension are required.');
        }

        static::runConsoleAction('migrate/up', ['migrationPath' => '@yii/rbac/migrations/', 'interactive' => false]);
    }

    public static function tearDownAfterClass()
    {
        static::runConsoleAction('migrate/down', ['migrationPath' => '@yii/rbac/migrations/', 'interactive' => false]);
        if (static::$db) {
            static::$db->close();
        }
        Leaps::$app = null;
        parent::tearDownAfterClass();
    }

    protected function setUp()
    {
        parent::setUp();
        $this->auth = $this->createManager();
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->auth->removeAll();
    }

    /**
     * @throws \Leaps\Base\InvalidParamException
     * @throws \Leaps\Db\Exception
     * @throws \Leaps\Base\InvalidConfigException
     * @return \Leaps\Db\Connection
     */
    public static function getConnection()
    {
        if (static::$db == null) {
            $db = new Connection;
            $db->dsn = static::$database['dsn'];
            if (isset(static::$database['username'])) {
                $db->username = static::$database['username'];
                $db->password = static::$database['password'];
            }
            if (isset(static::$database['attributes'])) {
                $db->attributes = static::$database['attributes'];
            }
            if (!$db->isActive) {
                $db->open();
            }
            static::$db = $db;
        }
        return static::$db;
    }

    /**
     * @return \Leaps\Rbac\ManagerInterface
     */
    protected function createManager()
    {
        return new DbManager(['db' => $this->getConnection()]);
    }
}
