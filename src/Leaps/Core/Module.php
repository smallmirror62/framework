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
	 * 模块基础路径
	 *
	 * @var string
	 */
	private $_basePath;

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
}