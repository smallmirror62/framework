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

class InlineAction extends Action
{
	/**
	 * 操作方法
	 *
	 * @var string the controller method that this inline action is associated with
	 */
	public $actionMethod;

	/**
	 * 构造方法
	 *
	 * @param string $id 操作ID
	 * @param Controller $controller 拥有该操作的控制器
	 * @param string $actionMethod 操作方法
	 * @param array $config 初始化配置
	 */
	public function __construct($id, $controller, $actionMethod, $config = [])
	{
		$this->actionMethod = $actionMethod;
		parent::__construct ( $id, $controller, $config );
	}

	/**
	 * 用指定的参数执行本操作
	 * 该方法主要由控制器调用。
	 *
	 * @param array $params 操作参数
	 * @return mixed 操作的结果
	 */
	public function runWithParams($params)
	{
		$args = $this->controller->bindActionParams ( $this, $params );
		Leaps::trace ( 'Running action: ' . get_class ( $this->controller ) . '::' . $this->actionMethod . '()', __METHOD__ );
		if (Leaps::app ()->requestedParams === null) {
			Leaps::app ()->requestedParams = $args;
		}
		return call_user_func_array ( [ $this->controller,$this->actionMethod ], $args );
	}
}
