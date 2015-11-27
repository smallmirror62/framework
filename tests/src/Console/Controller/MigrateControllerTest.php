<?php

namespace leapsunit\src\Console\Controller;

use Leaps;
use Leaps\Console\Controller\MigrateController;
use Leaps\Db\Migration;
use Leaps\Db\Query;
use leapsunit\TestCase;

/**
 * Unit test for [[\Leaps\Console\Controller\MigrateController]].
 * @see MigrateController
 *
 * @group console
 */
class MigrateControllerTest extends TestCase
{
    use MigrateControllerTestTrait;

    public function setUp()
    {
        $this->migrateControllerClass = EchoMigrateController::className();
        $this->migrationBaseClass = Migration::className();

        $this->mockApplication([
            'components' => [
                'db' => [
                    'class' => 'Leaps\Db\Connection',
                    'dsn' => 'sqlite::memory:',
                ],
            ],
        ]);

        $this->setUpMigrationPath();
        parent::setUp();
    }

    public function tearDown()
    {
        $this->tearDownMigrationPath();
        parent::tearDown();
    }

    /**
     * @return array applied migration entries
     */
    protected function getMigrationHistory()
    {
        $query = new Query();
        return $query->from('migration')->all();
    }
}