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

use Leaps\Kernel;

class Module extends Base
{
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
	 * 默认路由
	 *
	 * @var string
	 */
	public $defaultRoute = 'home';

	/**
	 * 控制器命名空间
	 *
	 * @var string
	 */
	public $controllerNamespace;

	/**
	 * 模块基础路径
	 *
	 * @var string
	 */
	private $_basePath;
	private $_viewPath;
	private $_layoutPath;

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
		$path = \Leaps\Kernel::getAlias ( $path );
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
		return Kernel::getAlias ( '@' . str_replace ( '\\', '/', $this->controllerNamespace ) );
	}

	/**
	 * 获取模块视图路径
	 * @return string the root directory of view files. Defaults to "[[basePath]]/views".
	 */
	public function getViewPath()
	{
		if ($this->_viewPath !== null) {
			return $this->_viewPath;
		} else {
			return $this->_viewPath = $this->getBasePath() . DIRECTORY_SEPARATOR . 'View';
		}
	}

	/**
	 * 设置模块视图路径
	 * @param string $path the root directory of view files.
	 * @throws InvalidParamException if the directory is invalid
	 */
	public function setViewPath($path)
	{
		$this->_viewPath = Kernel::getAlias($path);
	}

	/**
	 * 获取模块布局路径
	 * @return string the root directory of layout files. Defaults to "[[viewPath]]/layouts".
	 */
	public function getLayoutPath()
	{
		if ($this->_layoutPath !== null) {
			return $this->_layoutPath;
		} else {
			return $this->_layoutPath = $this->getViewPath() . DIRECTORY_SEPARATOR . 'layouts';
		}
	}

	/**
	 * 设置模块布局路径
	 * @param string $path the root directory or path alias of layout files.
	 * @throws InvalidParamException if the directory is invalid
	 */
	public function setLayoutPath($path)
	{
		$this->_layoutPath = Kernel::getAlias($path);
	}
}