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
namespace Leaps\Core;

use Leaps;

abstract class Application extends Module
{
	/**
	 * 应用程序名称
	 *
	 * @var string
	 */
	public $name = 'My Application';

	/**
	 * 应用程序编码
	 *
	 * @var string
	 */
	public $charset = 'UTF-8';

	/**
	 * 最终用户语言
	 *
	 * @var string
	 * @see sourceLanguage
	 */
	public $language = 'zh-CN';

	/**
	 * 应用程序使用的布局，false关闭
	 *
	 * @var string|boolean
	 */
	public $layout = 'main';

	/**
	 * 控制器命名空间
	 *
	 * @var string
	 */
	public $controllerNamespace = 'App\\Controller';

	/**
	 * 当前活跃的控制器实例
	 *
	 * @var Controller
	 */
	public $controller;

	/**
	 * 请求的路由
	 *
	 * @var string
	 */
	public $requestedRoute;

	/**
	 * 请求的操作
	 *
	 * @var Action
	 */
	public $requestedAction;

	/**
	 * 请求的参数
	 *
	 * @var array
	 */
	public $requestedParams;

	/**
	 * 已加载模块的类名称索引列表
	 *
	 * @var array
	 */
	public $loadedModules = [ ];

	/**
	 * 应用程序配置
	 *
	 * @var array 应用程序配置数组
	 */
	private $_config;

	/**
	 * 构造方法
	 *
	 * @param array $config
	 */
	public function __construct($config = [])
	{
		Leaps::setApp ( $this );
		$this->_config = $config;
		$this->preInit ();
		$this->registerErrorHandler($config);
		$this->setServices ( $this->_config ['services'] );
	}

	/**
	 * 前置初始化
	 *
	 * @param array $config
	 */
	public function preInit()
	{
		if (isset ( $this->_config ['basePath'] )) {
			$this->setBasePath ( $this->_config ['basePath'] );
			unset ( $this->_config ['basePath'] );
		} else {
			throw new InvalidConfigException ( 'The "basePath" configuration for the Application is required.' );
		}

		if (isset ( $this->_config ['vendorPath'] )) {
			$this->setVendorPath ( $this->_config ['vendorPath'] );
			unset ( $this->_config ['vendorPath'] );
		} else {
			// set "@vendor"
			$this->getVendorPath ();
		}

		if (isset ( $this->_config ['runtimePath'] )) {
			$this->setRuntimePath ( $this->_config ['runtimePath'] );
			unset ( $this->_config ['runtimePath'] );
		} else {
			// set "@runtime"
			$this->getRuntimePath ();
		}

		if (isset ( $this->_config ['timeZone'] )) {
			$this->setTimeZone ( $this->_config ['timeZone'] );
			unset ( $this->_config ['timeZone'] );
		} elseif (! ini_get ( 'date.timezone' )) {
			$this->setTimeZone ( 'UTC' );
		}

		// merge core components with custom services
		foreach ( $this->coreServices () as $id => $service ) {
			if (! isset ( $this->_config ['services'] [$id] )) {
				$this->_config ['services'] [$id] = $service;
			} elseif (is_array ( $this->_config ['services'] [$id] ) && ! isset ( $this->_config ['services'] [$id] ['className'] )) {
				$this->_config ['services'] [$id] ['className'] = $service ['className'];
			}
		}
	}

	/**
	 * 初始化错误处理
	 */
	public function registerErrorHandler()
	{
		if (LEAPS_ENABLE_ERROR_HANDLER) {
			if (! isset ( $this->_config ['services'] ['errorhandler'] ['className'] )) {
				echo "Error: no errorhandler service is configured.\n";
				exit ( 1 );
			}
			$this->set ( 'errorhandler', $this->_config ['services'] ['errorhandler'] );
			unset ( $this->_config ['services'] ['errorhandler'] );
			$this->getErrorhandler ()->register ();
		}
	}

	/**
	 * 返回应用程序的唯一ID
	 *
	 * @return string the unique ID of the module.
	 */
	public function getUniqueId()
	{
		return '';
	}

	/**
	 * 设置应用程序的根目录和@App别名。
	 *
	 * @param string $path 应用程序跟目录
	 * @property string 应用程序跟目录文件夹
	 * @throws InvalidParamException 如果文件夹不存在抛出异常
	 */
	public function setBasePath($path)
	{
		parent::setBasePath ( $path );
		Leaps::setAlias ( '@App', $this->getBasePath () );
		Leaps::setAlias ( '@Module', $this->getBasePath () . '/Module' );
	}

	/**
	 * 执行应用程序
	 */
	public function run()
	{
		try {
			$response = $this->handleRequest ( $this->getRequest () );
			$response->send ();
			return $response->exitStatus;
		} catch ( ExitException $e ) {
			return $e->statusCode;
		}
	}

	/**
	 * 处理指定的请求
	 *
	 * 此方法返回 [[Response]] 实例或其子类来表示处理请求的结果。
	 *
	 * @param Request $request the request to be handled
	 * @return Response the resulting response
	 */
	abstract public function handleRequest($request);

	/**
	 * 运行时文件目录
	 *
	 * @var string
	 */
	private $_runtimePath;

	/**
	 * 返回存储运行时文件的目录。
	 *
	 * @return
	 *
	 */
	public function getRuntimePath()
	{
		if ($this->_runtimePath === null) {
			$this->setRuntimePath ( $this->getBasePath () . DIRECTORY_SEPARATOR . 'Runtime' );
		}
		return $this->_runtimePath;
	}

	/**
	 * 设置存储运行时文件的目录。
	 *
	 * @param string $path
	 */
	public function setRuntimePath($path)
	{
		$this->_runtimePath = Leaps::getAlias ( $path );
		Leaps::setAlias ( '@Runtime', $this->_runtimePath );
	}

	/**
	 * 第三方组件目录
	 *
	 * @var string
	 */
	private $_vendorPath;

	/**
	 * 返回第三方组件目录
	 *
	 * @return string the directory that stores vendor files.
	 *         Defaults to "vendor" directory under [[basePath]].
	 */
	public function getVendorPath()
	{
		if ($this->_vendorPath === null) {
			$this->setVendorPath ( $this->getBasePath () . DIRECTORY_SEPARATOR . 'Vendor' );
		}
		return $this->_vendorPath;
	}

	/**
	 * 设置第三方组件目录
	 *
	 * @param string $path the directory that stores vendor files.
	 */
	public function setVendorPath($path)
	{
		$this->_vendorPath = Leaps::getAlias ( $path );
		Leaps::setAlias ( '@Vendor', $this->_vendorPath );
		Leaps::setAlias ( '@Bower', $this->_vendorPath . DIRECTORY_SEPARATOR . 'Bower' );
		Leaps::setAlias ( '@Npm', $this->_vendorPath . DIRECTORY_SEPARATOR . 'Npm' );
	}

	/**
	 * 返回应用程序时区
	 *
	 * @return string the time zone used by this application.
	 * @see http://php.net/manual/en/function.date-default-timezone-get.php
	 */
	public function getTimeZone()
	{
		return date_default_timezone_get ();
	}

	/**
	 * 设置当前应用所属时区
	 *
	 * @param string $value 应用程序使用的时区
	 * @see http://php.net/manual/en/function.date-default-timezone-set.php
	 */
	public function setTimeZone($value)
	{
		date_default_timezone_set ( $value );
	}

	/**
	 * 系统默认核心服务
	 *
	 * @return array
	 */
	public function coreServices()
	{
		return [
				'file' => ['className' => 'Leaps\Filesystem\Filesystem'],
				'log' => ['className' => 'Leaps\Log\Dispatcher'],
				'crypt' => ['className' => 'Leaps\Crypt\Crypt'],
				'cache' => ['className' => 'Leaps\Cache\ArrayCache'],
				'registry' => ['className' => 'Leaps\Core\Registry'],
				'filter' => ['className' => 'Leaps\Filter\Filter'],
				'event' => ['className' => 'Leaps\Events\Dispatcher'],
				'db'=>['className' => 'Leaps\Db\Db'],
				'view' => ['className' => 'Leaps\Web\View'],
				'cookie' => ['className' => 'Leaps\Http\Cookies'],
				'request' => ['className' => 'Leaps\Http\Request'],
				'response' => ['className' => 'Leaps\Http\Response'],
				'router' => ['className' => 'Leaps\Router\UrlManager'],
				'session' => ['className' => "\\Leaps\\Session\\Files"],
		];
	}

	/**
	 * Returns the database connection component.
	 * @return \Leaps\Db\Connection the database connection.
	 */
	public function getDb()
	{
		return $this->getShared('db');
	}

	/**
	 * Returns the cache component.
	 * @return \Leaps\Cache\Adapter the cache application component. Null if the component is not enabled.
	 */
	public function getCache()
	{
		return $this->getShared('cache');
	}

	/**
	 * Returns the log dispatcher component.
	 * @return \Leaps\Log\Dispatcher the log dispatcher application component.
	 */
	public function getLog()
	{
		return $this->getShared('log');
	}

	/**
	 * Returns the error handler component.
	 * @return \Leaps\Web\ErrorHandler|\Leaps\Console\ErrorHandler the error handler application component.
	 */
	public function getErrorHandler()
	{
		return $this->getShared('errorhandler');
	}

	/**
	 * 获取请求组件实例
	 *
	 * @return \Leaps\Http\Request|\Leaps\Console\Request
	 */
	public function getRequest()
	{
		return $this->getShared ( 'request' );
	}

	/**
	 * 获取响应组件实例
	 *
	 * @return \Leaps\Http\Response | \Leaps\Application\Console\Response
	 */
	public function getResponse()
	{
		return $this->getShared ( 'response' );
	}

	/**
	 * 返回视图对象
	 * @return View|\Leaps\Web\View the view application component that is used to render various view files.
	 */
	public function getView()
	{
		return $this->getShared('view');
	}

	/**
	 * 返回加密对象
	 * @return \Leaps\Crypt\Crypt the security application component.
	 */
	public function getCrypt()
	{
		return $this->getShared('crypt');
	}

	/**
	 * 返回应用URL路由对象
	 * @return \Leaps\Router\UrlManager the URL manager for this application.
	 */
	public function getUrlManager()
	{
		return $this->getShared('router');
	}

	/**
	 * 动态调用Di容器方法
	 *
	 * @param string $method
	 * @param array $args
	 * @return mixed
	 */
	public function __call($method, $args)
	{
		$instance = \Leaps\Di\Container::getDefault ();
		switch (count ( $args )) {
			case 0 :
				return $instance->$method ();
			case 1 :
				return $instance->$method ( $args [0] );
			case 2 :
				return $instance->$method ( $args [0], $args [1] );
			case 3 :
				return $instance->$method ( $args [0], $args [1], $args [2] );
			case 4 :
				return $instance->$method ( $args [0], $args [1], $args [2], $args [3] );
			default :
				return call_user_func_array ( [ $instance,$method ], $args );
		}
	}
}