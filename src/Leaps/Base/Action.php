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
namespace Leaps\Base;

use Leaps;

/**
 * Action is the base class for all controller action classes.
 *
 * Action provides a way to reuse action method code. An action method in an Action
 * class can be used in multiple controllers or in different projects.
 *
 * Derived classes must implement a method named `run()`. This method
 * will be invoked by the controller when the action is requested.
 * The `run()` method can have parameters which will be filled up
 * with user input values automatically according to their names.
 * For example, if the `run()` method is declared as follows:
 *
 * ~~~
 * public function run($id, $type = 'book') { ... }
 * ~~~
 *
 * And the parameters provided for the action are: `['id' => 1]`.
 * Then the `run()` method will be invoked as `run(1)` automatically.
 *
 * @property string $uniqueId The unique ID of this action among the whole application. This property is
 *           read-only.
 *          
 *          
 */
class Action extends Service
{
	/**
	 *
	 * @var string ID of the action
	 */
	public $id;
	/**
	 *
	 * @var Controller|\Leaps\Web\Controller the controller that owns this action
	 */
	public $controller;
	
	/**
	 * Constructor.
	 *
	 * @param string $id the ID of this action
	 * @param Controller $controller the controller that owns this action
	 * @param array $config name-value pairs that will be used to initialize the object properties
	 */
	public function __construct($id, $controller, $config = [])
	{
		$this->id = $id;
		$this->controller = $controller;
		parent::__construct ( $config );
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
		Leaps::trace ( 'Running action: ' . get_class ( $this ) . '::run()', __METHOD__ );
		if (Leaps::$app->requestedParams === null) {
			Leaps::$app->requestedParams = $args;
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
	 * This method is called right before `run()` is executed.
	 * You may override this method to do preparation work for the action run.
	 * If the method returns false, it will cancel the action.
	 *
	 * @return boolean whether to run the action.
	 */
	protected function beforeRun()
	{
		return true;
	}
	
	/**
	 * This method is called right after `run()` is executed.
	 * You may override this method to do post-processing work for the action run.
	 */
	protected function afterRun()
	{
	}
}