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

/**
 * ActionEvent represents the event parameter used for an action event.
 *
 * By setting the [[isValid]] property, one may control whether to continue running the action.
 */
class ActionEvent extends Event
{
	/**
	 *
	 * @var Action the action currently being executed
	 */
	public $action;
	/**
	 *
	 * @var mixed the action result. Event handlers may modify this property to change the action result.
	 */
	public $result;
	/**
	 *
	 * @var boolean whether to continue running the action. Event handlers of
	 *      [[Controller::EVENT_BEFORE_ACTION]] may set this property to decide whether
	 *      to continue running the current action.
	 */
	public $isValid = true;
	
	/**
	 * Constructor.
	 *
	 * @param Action $action the action associated with this action event.
	 * @param array $config name-value pairs that will be used to initialize the object properties
	 */
	public function __construct($action, $config = [])
	{
		$this->action = $action;
		parent::__construct ( $config );
	}
}