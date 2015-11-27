<?php

namespace leapsunit\src\web;

use Leaps;
use Leaps\Db\Connection;
use Leaps\Db\Query;
use yii\web\DbSession;
use leapsunit\TestCase;

/**
 * @group db
 */
class DbSessionTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
        Leaps::$app->set('db', [
            'class' => Connection::className(),
            'dsn' => 'sqlite::memory:',
        ]);
        Leaps::$app->db->createCommand()->createTable('session', [
            'id' => 'string',
            'expire' => 'integer',
            'data' => 'text',
            'user_id' => 'integer',
        ])->execute();
    }

    // Tests :

    public function testReadWrite()
    {
        $session = new DbSession();

        $session->writeSession('test', 'session data');
        $this->assertEquals('session data', $session->readSession('test'));
        $session->destroySession('test');
        $this->assertEquals('', $session->readSession('test'));
    }

    /**
     * @depends testReadWrite
     */
    public function testGarbageCollection()
    {
        $session = new DbSession();

        $session->writeSession('new', 'new data');
        $session->writeSession('expire', 'expire data');

        $session->db->createCommand()
            ->update('session', ['expire' => time() - 100], 'id = :id', ['id' => 'expire'])
            ->execute();
        $session->gcSession(1);

        $this->assertEquals('', $session->readSession('expire'));
        $this->assertEquals('new data', $session->readSession('new'));
    }

    /**
     * @depends testReadWrite
     */
    public function testWriteCustomField()
    {
        $session = new DbSession();
        $session->writeCallback = function ($session) {
            return [
                'user_id' => 15
            ];
        };

        $session->writeSession('test', 'session data');

        $query = new Query();
        $sessionRow = $query->from('session')
            ->where(['id' => 'test'])
            ->one();

        $this->assertEquals('session data', $sessionRow['data']);
        $this->assertEquals(15, $sessionRow['user_id']);
    }
}