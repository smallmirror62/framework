<?php

namespace leapsunit\src\Filter\Auth;

use Leaps;
use Leaps\Filter\Auth\HttpBasicAuth;
use Leaps\Filter\Auth\HttpBearerAuth;
use Leaps\Filter\Auth\QueryParamAuth;
use Leaps\Helper\ArrayHelper;
use Leaps\Rest\Controller;
use Leaps\Web\UnauthorizedHttpException;
use leapsunit\src\Filter\Stub\UserIdentity;

/**
 * @group filters
 * @author Dmitry Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.7
 */
class AuthTest extends \leapsunit\TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $_SERVER['SCRIPT_FILENAME'] = "/index.php";
        $_SERVER['SCRIPT_NAME'] = "/index.php";

        $appConfig = [
            'components' => [
                'user' => [
                    'identityClass' => UserIdentity::className()
                ],
            ],
            'controllerMap' => [
                'test-auth' => TestAuthController::className()
            ]
        ];

        $this->mockWebApplication($appConfig);
    }

    public function tokenProvider()
    {
        return [
            ['token1', 'user1'],
            ['token2', 'user2'],
            ['token3', 'user3'],
            ['unknown', null],
            [null, null],
        ];
    }

    public function authOnly($token, $login, $filter, $action)
    {
        /** @var TestAuthController $controller */
        $controller = Leaps::$app->createController('test-auth')[0];
        $controller->authenticatorConfig = ArrayHelper::merge($filter, ['only' => [$action]]);
        try {
            $this->assertEquals($login, $controller->run($action));
        } catch (UnauthorizedHttpException $e) {

        }
    }

    public function authOptional($token, $login, $filter, $action)
    {
        /** @var TestAuthController $controller */
        $controller = Leaps::$app->createController('test-auth')[0];
        $controller->authenticatorConfig = ArrayHelper::merge($filter, ['optional' => [$action]]);
        try {
            $this->assertEquals($login, $controller->run($action));
        } catch (UnauthorizedHttpException $e) {

        }
    }

    public function authExcept($token, $login, $filter, $action)
    {
        /** @var TestAuthController $controller */
        $controller = Leaps::$app->createController('test-auth')[0];
        $controller->authenticatorConfig = ArrayHelper::merge($filter, ['except' => ['other']]);
        try {
            $this->assertEquals($login, $controller->run($action));
        } catch (UnauthorizedHttpException $e) {

        }
    }

    /**
     * @dataProvider tokenProvider
     */
    public function testQueryParamAuth($token, $login) {
        $_GET['access-token'] = $token;
        $filter = ['className' => QueryParamAuth::className()];
        $this->authOnly($token, $login, $filter, 'query-param-auth');
        $this->authOptional($token, $login, $filter, 'query-param-auth');
        $this->authExcept($token, $login, $filter, 'query-param-auth');
    }

    /**
     * @dataProvider tokenProvider
     */
    public function testHttpBasicAuth($token, $login) {
        $_SERVER['PHP_AUTH_USER'] = $token;
        $_SERVER['PHP_AUTH_PW'] = 'whatever, we are testers';
        $filter = ['className' => HttpBasicAuth::className()];
        $this->authOnly($token, $login, $filter, 'basic-auth');
        $this->authOptional($token, $login, $filter, 'basic-auth');
        $this->authExcept($token, $login, $filter, 'basic-auth');
    }

    /**
     * @dataProvider tokenProvider
     */
    public function testHttpBasicAuthCustom($token, $login) {
        $_SERVER['PHP_AUTH_USER'] = $login;
        $_SERVER['PHP_AUTH_PW'] = 'whatever, we are testers';
        $filter = [
            'className' => HttpBasicAuth::className(),
            'auth' => function ($username, $password) {
                if (preg_match('/\d$/', $username)) {
                    return UserIdentity::findIdentity($username);
                }

                return null;
            }
        ];
        $this->authOnly($token, $login, $filter, 'basic-auth');
        $this->authOptional($token, $login, $filter, 'basic-auth');
        $this->authExcept($token, $login, $filter, 'basic-auth');
    }

    /**
     * @dataProvider tokenProvider
     */
    public function testHttpBearerAuth($token, $login) {
        Leaps::$app->request->headers->set('Authorization', "Bearer $token");
        $filter = ['className' => HttpBearerAuth::className()];
        $this->authOnly($token, $login, $filter, 'bearer-auth');
        $this->authOptional($token, $login, $filter, 'bearer-auth');
        $this->authExcept($token, $login, $filter, 'bearer-auth');
    }
}

/**
 * Class TestAuthController
 *
 * @author Dmitry Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.7
 */
class TestAuthController extends Controller
{
    public $authenticatorConfig = [];

    public function behaviors()
    {
        return ['authenticator' => $this->authenticatorConfig];
    }

    public function actionBasicAuth()
    {
        return Leaps::$app->user->id;
    }

    public function actionBearerAuth()
    {
        return Leaps::$app->user->id;
    }

    public function actionQueryParamAuth()
    {
        return Leaps::$app->user->id;
    }
}