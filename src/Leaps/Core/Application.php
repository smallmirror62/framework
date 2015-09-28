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
	 * 运行时文件目录
	 *
	 * @var string
	*/
	private $_runtimePath;

	/**
	 * 第三方组件目录
	 *
	 * @var string
	 */
	private $_vendorPath;

	/**
	 * 构造方法
	 *
	 * @param array $config
	 */
	public function __construct($client = 'Web',$config = [])
	{
		Kernel::setApp ( $this );
		if (! empty ( $config ) && is_array($config)) {
			foreach ( $config as $name => $value ) {
				$this->$name = $value;
			}
		}
		$this->preInit ( $config );
		$this->init ();
	}

	/**
	 * 前置初始化
	 * @param array $config
	 */
	public function preInit($config){

	}

	/**
	 * 执行应用程序
	 */
	public function run(){

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
}