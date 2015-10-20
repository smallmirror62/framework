<?php
// +----------------------------------------------------------------------
// | Leaps Framework [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2011-2014 Leaps Team (http://www.tintsoft.com)
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author XuTongle <xutongle@gmail.com>
// +----------------------------------------------------------------------
namespace Leaps\Web;

use Leaps;
use Leaps\Http\Response;

class Application extends \Leaps\Core\Application
{
	/**
	 *
	 * @var string the default route of this application. Defaults to 'site'.
	 */
	public $defaultRoute = 'site';

	/**
	 *
	 * @var Controller the currently active controller instance
	 */
	public $controller;

	/**
	 * (non-PHPdoc)
	 *
	 * @param resource Leaps\Http\Request
	 * @see \Leaps\Core\Application::handleRequest()
	 */
	public function handleRequest($request)
	{
		Leaps::setAlias ( '@Webroot', dirname ( $request->getScriptFile () ) );
		Leaps::setAlias ( '@Web', $request->getBaseUrl () );
		list ( $route, $params ) = $request->resolve ();
		try {
			Leaps::trace ( "Route requested: '$route'", __METHOD__ );
			$this->requestedRoute = $route;
			$result = $this->runAction ( $route, $params );
			if ($result instanceof Response) {
				return $result;
			} else {
				$response = $this->getShared ( 'response' );
				if ($result !== null) {
					$response->data = $result;
				}
				return $response;
			}
		} catch ( \Leaps\Router\Exception $e ) {
			throw new NotFoundHttpException ( 'Page not found.', $e->getCode (), $e );
		}
	}
	private $_homeUrl;

	/**
	 * 获取首页URL
	 *
	 * @return string
	 */
	public function getHomeUrl()
	{
		if ($this->_homeUrl === null) {
			if ($this->getRouter ()->showScriptName) {
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
	 * (non-PHPdoc)
	 *
	 * @see \Leaps\Core\Application::coreServices()
	 */
	public function coreServices()
	{
		return array_merge(parent::coreServices(),[
				"cookie" => [
						"className" => "\\Leaps\\Http\\Cookies"
				],
				"request" => [
						"className" => "\\Leaps\\Http\\Request"
				],
				"response" => [
						"className" => "\\Leaps\\Http\\Response"
				],
				'router' => [
						'className' => 'Leaps\Router\UrlManager'
				],
				'session' => [
						'className' => "\\Leaps\\Session\\Files"
				],
				'errorhandler' => [
						'className' => "\\Leaps\\Web\\ErrorHandler"
				]
		]);
	}
}