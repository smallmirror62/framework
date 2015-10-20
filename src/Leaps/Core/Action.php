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

class Action extends Base
{

	/**
	 * 操作 ID
	 *
	 * @var string
	 */
	public $id;

	/**
	 * 拥有该操作的控制器实例
	 *
	 * @var Controller|\Leaps\Core\Controller
	 */
	public $controller;

	/**
	 * 构造方法
	 *
	 * @param string $id 操作ID
	 * @param Controller $controller 拥有该操作的控制器实例
	 * @param array $config 初始化配置数组
	 */
	public function __construct($id, $controller, $config = [])
	{
		$this->id = $id;
		$this->controller = $controller;
		parent::__construct ( $config );
	}

	/**
	 * 返回应用的唯一标示
	 *
	 * @return string 返回应用程序唯一标示
	 */
	public function getUniqueId()
	{
		return $this->controller->getUniqueId () . '/' . $this->id;
	}

	/**
	 * 用指定的参数执行操作
	 * 该方法由控制器调用
	 *
	 * @param array $params 要绑定到Action的参数
	 * @return mixed Action执行结果
	 * @throws InvalidConfigException 如果Action类没有run()方法
	 */
	public function runWithParams($params)
	{
		if (! method_exists ( $this, 'run' )) {
			throw new InvalidConfigException ( get_class ( $this ) . ' must define a "run()" method.' );
		}
		$args = $this->controller->bindActionParams ( $this, $params );
		Leaps::trace ( 'Running action: ' . get_class ( $this ) . '::run()', __METHOD__ );
		if (Leaps::app ()->requestedParams === null) {
			Leaps::app ()->requestedParams = $args;
		}
		$result = call_user_func_array ( [ $this,'run' ], $args );
		return $result;
	}
}