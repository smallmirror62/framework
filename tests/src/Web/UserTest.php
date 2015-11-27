<?php


namespace yii\web;

/**
 * Mock for the time() function for web classes
 * @return int
 */
function time()
{
    return \leapsunit\src\web\UserTest::$time ?: \time();
}

namespace leapsunit\src\web;

use Leaps\Base\NotSupportedException;
use Leaps\Base\Component;
use yii\rbac\PhpManager;
use yii\web\IdentityInterface;
use yii\web\UrlManager;
use yii\web\UrlRule;
use yii\web\Request;
use Leaps;
use leapsunit\TestCase;

/**
 * @group web
 */
class UserTest extends TestCase
{
    /**
     * @var integer virtual time to be returned by mocked time() function.
     * Null means normal time() behavior.
     */
    public static $time;

    protected function tearDown()
    {
        Leaps::$app->session->removeAll();
        static::$time = null;
        parent::tearDown();
    }

    public function testLoginExpires()
    {
        if (getenv('TRAVIS') == 'true') {
            $this->markTestSkipped('Can not reliably test this on travis-ci.');
        }

        $appConfig = [
            'components' => [
                'user' => [
                    'identityClass' => UserIdentity::className(),
                    'authTimeout' => 10,
                ],
                'authManager' => [
                    'class' => PhpManager::className(),
                    'itemFile' => '@runtime/user_test_rbac_items.php',
                     'assignmentFile' => '@runtime/user_test_rbac_assignments.php',
                     'ruleFile' => '@runtime/user_test_rbac_rules.php',
                ]
            ],
        ];
        $this->mockWebApplication($appConfig);

        $am = Leaps::$app->authManager;
        $am->removeAll();
        $am->add($role = $am->createPermission('rUser'));
        $am->add($perm = $am->createPermission('doSomething'));
        $am->addChild($role, $perm);
        $am->assign($role, 'user1');

        Leaps::$app->session->removeAll();
        static::$time = \time();
        Leaps::$app->user->login(UserIdentity::findIdentity('user1'));

//        print_r(Leaps::$app->session);
//        print_r($_SESSION);

        $this->mockWebApplication($appConfig);
        $this->assertFalse(Leaps::$app->user->isGuest);
        $this->assertTrue(Leaps::$app->user->can('doSomething'));

        static::$time += 5;
        $this->mockWebApplication($appConfig);
        $this->assertFalse(Leaps::$app->user->isGuest);
        $this->assertTrue(Leaps::$app->user->can('doSomething'));

        static::$time += 11;
        $this->mockWebApplication($appConfig);
        $this->assertTrue(Leaps::$app->user->isGuest);
        $this->assertFalse(Leaps::$app->user->can('doSomething'));

    }

}

class UserIdentity extends Component implements IdentityInterface
{
    private static $ids = [
        'user1',
        'user2',
        'user3',
    ];

    private $_id;

    public static function findIdentity($id)
    {
        if (in_array($id, static::$ids)) {
            $identitiy = new static();
            $identitiy->_id = $id;
            return $identitiy;
        }
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException();
    }

    public function getId()
    {
        return $this->_id;
    }

    public function getAuthKey()
    {
        throw new NotSupportedException();
    }

    public function validateAuthKey($authKey)
    {
        throw new NotSupportedException();
    }
}