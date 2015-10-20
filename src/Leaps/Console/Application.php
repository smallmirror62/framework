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
namespace Leaps\Console;

class Application extends \Leaps\Core\Application
{
	/**
	 * (non-PHPdoc)
	 *
	 * @see \Leaps\Core\Application::handleRequest()
	 */
	public function handleRequest($request)
	{
		list ( $route, $params ) = $request->resolve ();
		$this->requestedRoute = $route;
		$result = $this->runAction ( $route, $params );
		if ($result instanceof Response) {
			return $result;
		} else {
			$response = $this->getResponse ();
			$response->exitStatus = $result;
			return $response;
		}
	}

	/**
	 * 运行一个控制器动作指定的路线。
	 *
	 * @param string $route the route that specifies the action.
	 * @param array $params the parameters to be passed to the action
	 * @return integer the status code returned by the action execution. 0 means
	 *         normal, and other values mean abnormal.
	 * @throws Exception if the route is invalid
	 */
	public function runAction($route, $params = [])
	{
		try {
			return ( int ) parent::runAction ( $route, $params );
		} catch ( \Exception $e ) {
			throw new Exception ( "Unknown command \"$route\".", 0, $e );
		}
	}

	/**
	 * 返回响应组件。
	 *
	 * @return Response the response component
	 */
	public function getResponse()
	{
		return $this->get ( 'response' );
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \Leaps\Core\Application::coreServices()
	 */
	public function coreServices()
	{
		return array_merge ( parent::coreServices (), [ 'request' => [ 'className' => 'Leaps\Console\Request' ],'response' => [ 'className' => 'Leaps\Console\Response' ],'errorHandler' => [ 'className' => 'Leaps\Console\ErrorHandler' ] ]

		 );
	}
}