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

class Module extends Base
{
	/**
	 * 自定义模块配置
	 * 
	 * @var array custom module parameters (name => value).
	 */
	public $params = [ ];
	
	/**
	 * 当前模块ID
	 *
	 * @var string
	 */
	public $id;
	
	/**
	 * 父模块实例
	 *
	 * @var Module
	 */
	public $module;
	
	/**
	 * 模块布局
	 *
	 * @var string|boolean
	 */
	public $layout;
	
	/**
	 * 默认路由
	 *
	 * @var string
	 */
	public $defaultRoute = 'index';
	
	/**
	 * 控制器集合
	 *
	 * @var array
	 */
	public $controllerMap = [ ];
	
	/**
	 * 控制器命名空间
	 *
	 * @var string
	 */
	public $controllerNamespace;
	
	/**
	 * 模块基础路径
	 *
	 * @var string $_basePath
	 */
	private $_basePath;
	
	/**
	 * 视图文件路径
	 *
	 * @var string $_viewPath
	 */
	private $_viewPath;
	
	/**
	 * 布局文件路径
	 *
	 * @var string $_layoutPath
	 */
	private $_layoutPath;
	
	/**
	 * 已经注册的子模块
	 *
	 * @var array
	 */
	private $_modules = [ ];
	
	/**
	 * 构造方法
	 *
	 * @param string $id 模块ID
	 * @param Module $parent 父模块实例
	 * @param array $config 模块配置
	 */
	public function __construct($id, $parent = null, $config = [])
	{
		$this->id = $id;
		$this->module = $parent;
		parent::__construct ( $config );
	}
	
	/**
	 * 初始化模块
	 */
	public function init()
	{
		if ($this->controllerNamespace === null) {
			$class = get_class ( $this );
			if (($pos = strrpos ( $class, '\\' )) !== false) {
				$this->controllerNamespace = substr ( $class, 0, $pos ) . '\\Controller';
			}
		}
	}
	
	/**
	 * 返回唯一的应用程序标识
	 *
	 * @return string 该模块的唯一ID
	 */
	public function getUniqueId()
	{
		return $this->module ? ltrim ( $this->module->getUniqueId () . '/' . $this->id, '/' ) : $this->id;
	}
	
	/**
	 * 返回模块跟文件夹
	 *
	 * @return string the root directory of the module.
	 */
	public function getBasePath()
	{
		if ($this->_basePath === null) {
			$class = new \ReflectionClass ( $this );
			$this->_basePath = dirname ( $class->getFileName () );
		}
		return $this->_basePath;
	}
	
	/**
	 * 设置模块文件夹
	 *
	 * @param string $path the root directory of the module. This can be either a directory name or a path alias.
	 * @throws InvalidParamException if the directory does not exist.
	 */
	public function setBasePath($path)
	{
		$path = Leaps::getAlias ( $path );
		$p = realpath ( $path );
		if ($p !== false && is_dir ( $p )) {
			$this->_basePath = $p;
		} else {
			throw new InvalidParamException ( "The directory does not exist: $path" );
		}
	}
	
	/**
	 * 获取控制器路径
	 *
	 * @return string 包含控制器类的目录
	 * @throws InvalidParamException if there is no alias defined for the root namespace of [[controllerNamespace]].
	 */
	public function getControllerPath()
	{
		return Leaps::getAlias ( '@' . str_replace ( '\\', '/', $this->controllerNamespace ) );
	}
	
	/**
	 * 获取模块视图路径
	 *
	 * @return string the root directory of view files. Defaults to "[[basePath]]/views".
	 */
	public function getViewPath()
	{
		if ($this->_viewPath !== null) {
			return $this->_viewPath;
		} else {
			return $this->_viewPath = $this->getBasePath () . DIRECTORY_SEPARATOR . 'View';
		}
	}
	
	/**
	 * 设置模块视图路径
	 *
	 * @param string $path the root directory of view files.
	 * @throws InvalidParamException if the directory is invalid
	 */
	public function setViewPath($path)
	{
		$this->_viewPath = Leaps::getAlias ( $path );
	}
	
	/**
	 * 获取模块布局路径
	 *
	 * @return string the root directory of layout files. Defaults to "[[viewPath]]/Layout".
	 */
	public function getLayoutPath()
	{
		if ($this->_layoutPath !== null) {
			return $this->_layoutPath;
		} else {
			return $this->_layoutPath = $this->getViewPath () . DIRECTORY_SEPARATOR . 'Layout';
		}
	}
	
	/**
	 * 设置模块布局路径
	 *
	 * @param string $path the root directory or path alias of layout files.
	 * @throws InvalidParamException if the directory is invalid
	 */
	public function setLayoutPath($path)
	{
		$this->_layoutPath = Leaps::getAlias ( $path );
	}
	
	/**
	 * 设置别名路径
	 * For example,
	 *
	 * ~~~
	 * [
	 * '@models' => '@app/models', // an existing alias
	 * '@backend' => __DIR__ . '/../backend', // a directory
	 * ]
	 * ~~~
	 */
	public function setAliases($aliases)
	{
		foreach ( $aliases as $name => $alias ) {
			Leaps::setAlias ( $name, $alias );
		}
	}
	
	/**
	 * 检查是否存在指定的子模块
	 *
	 * @param string $id module ID. For grand child modules, use ID path relative to this module (e.g. `admin/content`).
	 * @return boolean whether the named module exists. Both loaded and unloaded modules
	 *         are considered.
	 */
	public function hasModule($id)
	{
		if (($pos = strpos ( $id, '/' )) !== false) {
			$module = $this->getModule ( substr ( $id, 0, $pos ) );
			return $module === null ? false : $module->hasModule ( substr ( $id, $pos + 1 ) );
		} else {
			return isset ( $this->_modules [$id] );
		}
	}
	
	/**
	 * 检索指定名称的子模块。
	 *
	 * @param string $id module ID (case-sensitive). To retrieve grand child modules,
	 *        use ID path relative to this module (e.g. `admin/content`).
	 * @param boolean $load whether to load the module if it is not yet loaded.
	 * @return Module|null the module instance, null if the module does not exist.
	 * @see hasModule()
	 */
	public function getModule($id, $load = true)
	{
		if (($pos = strpos ( $id, '/' )) !== false) {
			$module = $this->getModule ( substr ( $id, 0, $pos ) );
			return $module === null ? null : $module->getModule ( substr ( $id, $pos + 1 ), $load );
		}
		if (isset ( $this->_modules [$id] )) {
			if ($this->_modules [$id] instanceof Module) {
				return $this->_modules [$id];
			} elseif ($load) {
				Leaps::trace ( "Loading module: $id", __METHOD__ );
				/* @var $module Module */
				$module = Leaps::createObject ( $this->_modules [$id], [ 
					$id,
					$this 
				] );
				return $this->_modules [$id] = $module;
			}
		}
		return null;
	}
	
	/**
	 * 添加子模块到当前模块
	 *
	 * @param string $id 模块ID
	 * @param Module|array|null $module the sub-module to be added to this module. This can
	 *        be one of the followings:
	 *       
	 *        - a [[Module]] object
	 *        - a configuration array: when [[getModule()]] is called initially, the array
	 *        will be used to instantiate the sub-module
	 *        - null: the named sub-module will be removed from this module
	 */
	public function setModule($id, $module)
	{
		if ($module === null) {
			unset ( $this->_modules [$id] );
		} else {
			$this->_modules [$id] = $module;
		}
	}
	
	/**
	 * 返回当前模块的子模块
	 *
	 * @param boolean $loadedOnly whether to return the loaded sub-modules only. If this is set false,
	 *        then all sub-modules registered in this module will be returned, whether they are loaded or not.
	 *        Loaded modules will be returned as objects, while unloaded modules as configuration arrays.
	 * @return array the modules (indexed by their IDs)
	 */
	public function getModules($loadedOnly = false)
	{
		if ($loadedOnly) {
			$modules = [ ];
			foreach ( $this->_modules as $module ) {
				if ($module instanceof Module) {
					$modules [] = $module;
				}
			}
			return $modules;
		} else {
			return $this->_modules;
		}
	}
	
	/**
	 * 批量注册子模块
	 *
	 * If a new sub-module has the same ID as an existing one, the existing one will be overwritten silently.
	 *
	 * The following is an example for registering two sub-modules:
	 *
	 * ~~~
	 * [
	 * 'comment' => [
	 * 'className' => 'app\modules\comment\CommentModule',
	 * 'db' => 'db',
	 * ],
	 * 'booking' => ['className' => 'app\modules\booking\BookingModule'],
	 * ]
	 * ~~~
	 *
	 * @param array $modules modules (id => module configuration or instances)
	 */
	public function setModules($modules)
	{
		foreach ( $modules as $id => $module ) {
			$this->_modules [$id] = $module;
		}
	}
	
	/**
	 * 从路由执行控制器操作
	 *
	 * @param string $route 操作路由
	 * @param array $params 参数
	 * @return mixed 操作结果
	 * @throws InvalidRouteException if the requested route cannot be resolved into an action successfully
	 */
	public function runAction($route, $params = [])
	{
		$parts = $this->createController ( $route );
		if (is_array ( $parts )) {
			/* @var $controller Controller */
			list ( $controller, $actionID ) = $parts;
			$oldController = Leaps::app ()->controller;
			Leaps::app ()->controller = $controller;
			$result = $controller->runAction ( $actionID, $params );
			Leaps::app ()->controller = $oldController;
			return $result;
		} else {
			throw new \Leaps\Router\Exception ( 'Unable to resolve the request "' . $route . '".' );
		}
	}
	
	/**
	 * 根据控制器ID创建控制器
	 *
	 * 控制器标识是相对于该模块的。
	 *
	 * @param string $id 控制器ID
	 * @return Controller the newly created controller instance, or null if the controller ID is invalid.
	 * @throws InvalidConfigException if the controller class and its file name do not match.
	 *         This exception is only thrown when in debug mode.
	 */
	public function createController($route)
	{
		if ($route === '') {
			$route = $this->defaultRoute;
		}
		
		// double slashes or leading/ending slashes may cause substr problem
		$route = trim ( $route, '/' );
		if (strpos ( $route, '//' ) !== false) {
			return false;
		}
		
		if (strpos ( $route, '/' ) !== false) {
			list ( $id, $route ) = explode ( '/', $route, 2 );
		} else {
			$id = $route;
			$route = '';
		}
		
		// module and controller map take precedence
		if (isset ( $this->controllerMap [$id] )) {
			$controller = Leaps::createObject ( $this->controllerMap [$id], [ 
				$id,
				$this 
			] );
			return [ 
				$controller,
				$route 
			];
		}
		
		$module = $this->getModule ( $id );
		
		if ($module !== null) {
			return $module->createController ( $route );
		}
		
		if (($pos = strrpos ( $route, '/' )) !== false) {
			$id .= '/' . substr ( $route, 0, $pos );
			$route = substr ( $route, $pos + 1 );
		}
		$controller = $this->createControllerByID ( $id );
		if ($controller === null && $route !== '') {
			$controller = $this->createControllerByID ( $id . '/' . $route );
			$route = '';
		}
		
		return $controller === null ? false : [ 
			$controller,
			$route 
		];
	}
	
	/**
	 * 根据控制器ID创建控制器
	 *
	 * @param string $id 控制器ID
	 * @return Controller the newly created controller instance, or null if the controller ID is invalid.
	 * @throws InvalidConfigException if the controller class and its file name do not match.
	 *         This exception is only thrown when in debug mode.
	 */
	public function createControllerByID($id)
	{
		$pos = strrpos ( $id, '/' );
		if ($pos === false) {
			$prefix = '';
			$className = $id;
		} else {
			$prefix = substr ( $id, 0, $pos + 1 );
			$className = substr ( $id, $pos + 1 );
		}
		if (! preg_match ( '%^[a-z][a-z0-9\\-_]*$%', $className )) {
			return null;
		}
		if ($prefix !== '' && ! preg_match ( '%^[a-z0-9_/]+$%i', $prefix )) {
			return null;
		}
		$className = str_replace ( ' ', '', ucwords ( str_replace ( '-', ' ', $className ) ) ) . 'Controller';
		$className = ltrim ( $this->controllerNamespace . '\\' . str_replace ( '/', '\\', $prefix ) . $className, '\\' );
		if (strpos ( $className, '-' ) !== false || ! class_exists ( $className )) {
			return null;
		}
		if (is_subclass_of ( $className, 'Leaps\Core\Controller' )) {
			return Leaps::createObject ( $className, [ 
				$id,
				$this 
			] );
		} elseif (LEAPS_DEBUG) {
			throw new InvalidConfigException ( "Controller class must extend from \\Leaps\\Core\\Controller." );
		} else {
			return null;
		}
	}
}