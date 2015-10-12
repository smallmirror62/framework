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

class Action extends Base
{

	/**
	 * Action ID
	 *
	 * @var string
	 */
	public $id;

	/**
	 * the controller that owns this action
	 *
	 * @var Controller|\Leaps\Core\Controller
	 */
	public $controller;

	/**
	 * 构造方法
	 *
	 * @param string $id the ID of this action
	 * @param Controller $controller the controller that owns this action
	 * @param array $config name-value pairs that will be used to initialize the object properties
	 */
	public function __construct($id, $controller, $config = [])
	{
		$this->id = $id;
		$this->controller = $controller;
		parent::__construct($config);
	}

	/**
	 * Returns the unique ID of this action among the whole application.
	 *
	 * @return string the unique ID of this action among the whole application.
	 */
	public function getUniqueId()
	{
		return $this->controller->getUniqueId () . '/' . $this->id;
	}

	/**
	 * Runs this action with the specified parameters.
	 * This method is mainly invoked by the controller.
	 *
	 * @param array $params the parameters to be bound to the action's run() method.
	 * @return mixed the result of the action
	 * @throws InvalidConfigException if the action class does not have a run() method
	 */
	public function runWithParams($params)
	{
		if (! method_exists ( $this, 'run' )) {
			throw new InvalidConfigException ( get_class ( $this ) . ' must define a "run()" method.' );
		}
		$args = $this->controller->bindActionParams ( $this, $params );
		Kernel::trace ( 'Running action: ' . get_class ( $this ) . '::run()', __METHOD__ );
		if (Kernel::$app->requestedParams === null) {
			Kernel::$app->requestedParams = $args;
		}
		if ($this->beforeRun ()) {
			$result = call_user_func_array ( [
					$this,
					'run'
			], $args );
			$this->afterRun ();
			return $result;
		} else {
			return null;
		}
	}

	/**
	 * 前置执行
	 *
	 * @return boolean whether to run the action.
	 */
	protected function beforeRun()
	{
		return true;
	}

	/**
	 * 后置执行
	 */
	protected function afterRun()
	{
	}
}