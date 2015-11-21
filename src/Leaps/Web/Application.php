<?php

/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2015 TintSoft
 * @license http://www.tintsoft.com/license/
 */
namespace Leaps\Web;

use Leaps;
use Leaps\Base\InvalidRouteException;

/**
 * Application is the base class for all web application classes.
 *
 * @property string $homeUrl Home Url
 * @property \Leaps\Web\Session $session Session组件，此属性为只读。
 * @property \Leaps\Web\User $user 用户组件，此属性为只读。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Application extends \Leaps\Base\Application
{
	/**
	 * 应用路由
	 *
	 * @var string 默认为 'site'.
	 */
	public $defaultRoute = 'site';

	/**
	 * 定义一个控制器处理所有请求
	 *
	 * @var array the configuration specifying a controller action which should handle
	 *      all user requests. This is mainly used when the application is in maintenance mode
	 *      and needs to handle all incoming requests via a single action.
	 *      The configuration is an array whose first element specifies the route of the action.
	 *      The rest of the array elements (key-value pairs) specify the parameters to be bound
	 *      to the action. For example,
	 *
	 *      ~~~
	 *      [
	 *      'offline/notice',
	 *      'param1' => 'value1',
	 *      'param2' => 'value2',
	 *      ]
	 *      ~~~
	 *
	 *      Defaults to null, meaning catch-all is not used.
	 */
	public $catchAll;

	/**
	 * 当前活动的控制器实例
	 *
	 * @var Controller
	 */
	public $controller;

	/**
	 * @inheritdoc
	 */
	protected function bootstrap()
	{
		$request = $this->getRequest ();
		Leaps::setAlias ( '@webroot', dirname ( $request->getScriptFile () ) );
		Leaps::setAlias ( '@web', $request->getBaseUrl () );
		parent::bootstrap ();
	}

	/**
	 * 处理指定的请求
	 *
	 * @param Request $request 请求实例
	 * @return Response 产生的响应
	 * @throws NotFoundHttpException 如果请求的路由无效
	 */
	public function handleRequest($request)
	{
		if (empty ( $this->catchAll )) {
			list ( $route, $params ) = $request->resolve ();
		} else {
			$route = $this->catchAll [0];
			$params = $this->catchAll;
			unset ( $params [0] );
		}
		try {
			Leaps::trace ( "Route requested: '$route'", __METHOD__ );
			$this->requestedRoute = $route;
			$result = $this->runAction ( $route, $params );
			if ($result instanceof Response) {
				return $result;
			} else {
				$response = $this->getResponse ();
				if ($result !== null) {
					$response->data = $result;
				}

				return $response;
			}
		} catch ( InvalidRouteException $e ) {
			throw new NotFoundHttpException ( Leaps::t ( 'leaps', 'Page not found.' ), $e->getCode (), $e );
		}
	}
	private $_homeUrl;

	/**
	 * 首页URL
	 *
	 * @return string
	 */
	public function getHomeUrl()
	{
		if ($this->_homeUrl === null) {
			if ($this->getUrlManager ()->showScriptName) {
				return $this->getRequest ()->getScriptUrl ();
			} else {
				return $this->getRequest ()->getBaseUrl () . '/';
			}
		} else {
			return $this->_homeUrl;
		}
	}

	/**
	 * 设置首页URL
	 *
	 * @param string $value
	 */
	public function setHomeUrl($value)
	{
		$this->_homeUrl = $value;
	}

	/**
	 * 返回Session组件
	 *
	 * @return Session the session component.
	 */
	public function getSession()
	{
		return $this->get ( 'session' );
	}

	/**
	 * 返回用户组件
	 *
	 * @return User the user component.
	 */
	public function getUser()
	{
		return $this->get ( 'user' );
	}

	/**
	 * @inheritdoc
	 */
	public function coreServices()
	{
		return array_merge ( parent::coreServices (), [ 'request' => [ 'className' => 'Leaps\Web\Request' ],'response' => [ 'className' => 'Leaps\Web\Response' ],'session' => [ 'className' => 'Leaps\Web\Session' ],'user' => [ 'className' => 'Leaps\Web\User' ],'errorHandler' => [ 'className' => 'Leaps\Web\ErrorHandler' ] ] );
	}
}
