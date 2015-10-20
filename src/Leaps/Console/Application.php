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
use Leaps;
class Application extends \Leaps\Core\Application
{

	/**
	 * 指定程序配置文件的参数
	 * @var string
	 */
	const OPTION_APPCONFIG = 'appconfig';

	/**
	 * 默认路由
	 * @var string
	 */
	public $defaultRoute = 'help';

	public $controllerNamespace = 'App\Console\Controller';

	/**
	 * 是否启用核心框架提供的命令
	 * @var boolean 默认是true
	 */
	public $enableCoreCommands = true;

	/**
	 * 控制器实例
	 * @var Controller
	 */
	public $controller;

	/**
	 * @inheritdoc
	 */
	public function __construct($config = [])
	{
		$config = $this->loadConfig($config);
		parent::__construct($config);
	}

	/**
	 * 加载配置
	 * 如果启动时指定参数 [[OPTION_APPCONFIG]] 则加载该文件
	 * @param array $config the configuration provided in the constructor.
	 * @return array the actual configuration to be used by the application.
	 */
	protected function loadConfig($config)
	{
		if (!empty($_SERVER['argv'])) {
			$option = '--' . self::OPTION_APPCONFIG . '=';
			foreach ($_SERVER['argv'] as $param) {
				if (strpos($param, $option) !== false) {
					$path = substr($param, strlen($option));
					if (!empty($path) && is_file($file = Leaps::getAlias($path))) {
						return require($file);
					} else {
						die("The configuration file does not exist: $path\n");
					}
				}
			}
		}
		return $config;
	}

	/**
	 * 初始化应用
	 */
	public function init()
	{
		parent::init();
		if ($this->enableCoreCommands) {
			foreach ($this->coreCommands() as $id => $command) {
				if (!isset($this->controllerMap[$id])) {
					$this->controllerMap[$id] = $command;
				}
			}
		}
		// ensure we have the 'help' command so that we can list the available commands
		if (!isset($this->controllerMap['help'])) {
			$this->controllerMap['help'] = 'Leaps\Console\Controller\HelpController';
		}
	}

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
	 * 返回核心命令配置
	 * @return array the configuration of the built-in commands.
	 */
	public function coreCommands()
	{
		return [
				'help' => 'Leaps\Console\Controller\HelpController',
				'cache' => 'Leaps\Console\Controller\CacheController'
		];
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \Leaps\Core\Application::coreServices()
	 */
	public function coreServices()
	{
		return array_merge ( parent::coreServices (), [ 'request' => [ 'className' => 'Leaps\Console\Request' ],'response' => [ 'className' => 'Leaps\Console\Response' ],'errorhandler' => [ 'className' => 'Leaps\Console\ErrorHandler' ] ]

		 );
	}
}