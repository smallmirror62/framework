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
namespace Leaps\Di;

class Container implements \ArrayAccess, ContainerInterface
{
	private $_services;
	protected $_sharedInstances;
	protected $_freshInstance = false;
	protected static $_default;
	/**
	 * 构造方法
	 */
	public function __construct(array $config = [])
	{
		if (! static::$_default) {
			static::$_default = $this;
		}
	}

	/**
	 * 注册一个服务到服务容器
	 *
	 * @param string name 服务名称
	 * @param mixed definition 服务定义
	 * @param boolean shared
	 * @return Leaps\Di\ServiceInterface
	 */
	public function set($name, $definition, $shared = false)
	{
		$service = new Service ( $name, $definition, $shared );
		$this->_services [$name] = $service;
		return $service;
	}

	/**
	 * Registers an "always shared" service in the services container
	 *
	 * @param string name 服务名称
	 * @param mixed definition 服务定义
	 * @return Leaps\Di\ServiceInterface
	 */
	public function setShared($name, $definition)
	{
		$service = new Service ( $name, $definition, true );
		$this->_services [$name] = $service;
		return $service;
	}

	/**
	 * 从容器中删除服务
	 *
	 * @param string name 服务名称
	 */
	public function remove($name)
	{
		unset ( $this->_services [$name] );
	}

	/**
	 * Attempts to register a service in the services container
	 * Only is successful if a service hasn"t been registered previously
	 * with the same name
	 *
	 * @param string name 服务器名称
	 * @param mixed definition 服务定义
	 * @param boolean shared 是否共享
	 * @return Leaps\Di\ServiceInterface|false
	 */
	public function attempt($name, $definition, $shared = false)
	{
		if (! isset ( $this->_services [$name] )) {
			$service = new Service ( $name, $definition, $shared );
			$this->_services [$name] = $service;
			return $service;
		}
		return false;
	}

	/**
	 * Sets a service using a raw Leaps\Di\Service definition
	 *
	 * @param string name 服务名称
	 * @param Leaps\Di\ServiceInterface rawDefinition
	 * @return Leaps\Di\ServiceInterface
	 */
	public function setRaw($name, ServiceInterface $rawDefinition)
	{
		$this->_services [$name] = $rawDefinition;
		return $rawDefinition;
	}

	/**
	 * Returns a service definition without resolving
	 *
	 * @param string name
	 * @return mixed
	 */
	public function getRaw($name)
	{
		if (isset ( $this->_services [$name] )) {
			return $this->_services [$name]->getDefinition ();
		}
		throw new Exception ( "Service '" . $name . "' wasn't found in the dependency injection container" );
	}

	/**
	 * 返回 Leaps\Di\Service 实例
	 *
	 * @param string name
	 * @return Leaps\Di\ServiceInterface
	 */
	public function getService($name)
	{
		if (isset ( $this->_services [$name] )) {
			return $this->_services [$name];
		}
		throw new Exception ( "Service '" . $name . "' wasn't found in the dependency injection container" );
	}

	/**
	 * 通过配置文件解析服务配置
	 *
	 * @param string name
	 * @param array parameters
	 * @return mixed
	 */
	public function get($name, $parameters = null)
	{
		if (isset ( $this->_services [$name] )) {
			/**
			 * 服务已经注册
			 */
			$instance = $this->_services [$name]->resolve ( $parameters );
		} else {
			throw new Exception ( "Service '" . $name . "' wasn't found in the dependency injection container" );
		}

		/**
		 * 如果实现了初始化接口就执行初始化
		 * 实现DI的类不能直接初始化，因为在构造方法中执行init还没有把DI实例射进去，这时候如果使用DI容器里的服务会抛异常
		 */
		if (method_exists ( $instance, "init" )) {
			$instance->init ();
		}

		return $instance;
	}

	/**
	 * Resolves a service, the resolved service is stored in the DI, subsequent requests for this service will return the same instance
	 *
	 * @param string name
	 * @param array parameters
	 * @return mixed
	 */
	public function getShared($name, $parameters = null)
	{
		/**
		 * This method provides a first level to shared instances allowing to use non-shared services as shared
		 */
		if (isset ( $this->_sharedInstances [$name] )) {
			$instance = $this->_sharedInstances [$name];
			$this->_freshInstance = false;
		} else {
			/**
			 * Resolve the instance normally
			 */
			$instance = $this->get ( $name, $parameters );
			/**
			 * Save the instance in the first level shared
			 */
			$this->_sharedInstances [$name] = $instance;
			$this->_freshInstance = true;
		}
		return $instance;
	}

	/**
	 * Check whether the DI contains a service by a name
	 *
	 * @param string name
	 * @return boolean
	 */
	public function has($name)
	{
		return isset ( $this->_services [$name] );
	}

	/**
	 * Check whether the last service obtained via getShared produced a fresh instance or an existing one
	 *
	 * @return boolean
	 */
	public function wasFreshInstance()
	{
		return $this->_freshInstance;
	}

	/**
	 * 返回服务列表
	 *
	 * @return Leaps\Di\Service[]
	 */
	public function getServices()
	{
		return $this->_services;
	}

	/**
	 * Check if a service is registered using the array syntax
	 *
	 * @param string name
	 * @return boolean
	 */
	public function offsetExists($name)
	{
		return $this->has ( $name );
	}

	/**
	 * Allows to register a shared service using the array syntax
	 *
	 * <code>
	 * $di["request"] = new \Leaps\Http\Request();
	 * </code>
	 *
	 * @param string name
	 * @param mixed definition
	 * @return boolean
	 */
	public function offsetSet($name, $definition)
	{
		$this->setShared ( $name, $definition );
		return true;
	}

	/**
	 * Allows to obtain a shared service using the array syntax
	 *
	 * <code>
	 * var_dump($di["request"]);
	 * </code>
	 *
	 * @param string name
	 * @return mixed
	 */
	public function offsetGet($name)
	{
		return $this->getShared ( $name );
	}

	/**
	 * Removes a service from the services container using the array syntax
	 *
	 * @param string name
	 */
	public function offsetUnset($name)
	{
		return false;
	}

	/**
	 * Magic method to get or set services using setters/getters
	 *
	 * @param string method
	 * @param array arguments
	 * @return mixed
	 */
	public function __call($method, $arguments = null)
	{
		/**
		 * If the magic method starts with "get" we try to get a service with that name
		 */
		if (substr ( $method, 0, 3 ) == "get") {
			$possibleService = lcfirst ( substr ( $method, 3 ) );
			if (isset ( $this->_services [$possibleService] )) {
				if (count ( $arguments )) {
					$instance = $this->get ( $possibleService, $arguments );
				} else {
					$instance = $this->get ( $possibleService );
				}
				return $instance;
			}
		}

		/**
		 * If the magic method starts with "set" we try to set a service using that name
		 */
		if (substr ( $method, 0, 3 ) == "set") {
			if (isset ( $arguments [0] )) {
				$this->set ( lcfirst ( substr ( $method, 3 ) ), $arguments [0] );
				return null;
			}
		}

		/**
		 * The method doesn't start with set/get throw an exception
		 */
		throw new Exception ( "Call to undefined method or service '" . $method . "'" );
	}

	/**
	 * 批量注册服务到容器
	 *
	 * The following is an example for registering two component definitions:
	 *
	 * ```php
	 * [
	 * 'db' => [
	 * 'className' => 'Leaps\Db\Connection',
	 * 'dsn' => 'sqlite:path/to/file.db',
	 * ],
	 * 'cache' => [
	 * 'className' => 'Leaps\Cache\DbCache',
	 * 'db' => 'db',
	 * ],
	 * ]
	 * ```
	 *
	 * @param array $services service definitions or instances
	 */
	public function setServices($services = [])
	{
		foreach ( $services as $id => $service ) {
			$this->setShared ( $id, $service );
		}
	}

	/**
	 * 设置Di实例
	 *
	 * @param Leaps\Di\ContainerInterface dependencyInjector
	 */
	public static function setDefault(ContainerInterface $dependencyInjector)
	{
		static::$_default = $dependencyInjector;
	}

	/**
	 * 返回Di单例
	 *
	 * @return Leaps\Di\ContainerInterface
	 */
	public static function getDefault()
	{
		if (! static::$_default) {
			static::$_default = new static ();
		}
		return static::$_default;
	}

	/**
	 * 销毁Di单例
	 */
	public static function reset()
	{
		static::$_default = null;
	}
}