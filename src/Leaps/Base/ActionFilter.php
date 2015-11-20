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
 * ActionFilter 操作过滤器
 *
 * An action filter will participate in the action execution workflow by responding to
 * the `beforeAction` and `afterAction` events triggered by modules and controllers.
 *
 * Check implementation of [[\Leaps\Filter\AccessControl]], [[\Leaps\Filter\PageCache]] and [[\Leaps\Filter\HttpCache]] as examples on how to use it.
 */
class ActionFilter extends Behavior
{
	/**
	 *
	 * @var array list of action IDs that this filter should apply to. If this property is not set,
	 *      then the filter applies to all actions, unless they are listed in [[except]].
	 *      If an action ID appears in both [[only]] and [[except]], this filter will NOT apply to it.
	 *     
	 *      Note that if the filter is attached to a module, the action IDs should also include child module IDs (if any)
	 *      and controller IDs.
	 *     
	 * @see except
	 */
	public $only;
	/**
	 *
	 * @var array list of action IDs that this filter should not apply to.
	 * @see only
	 */
	public $except = [ ];
	
	/**
	 * @inheritdoc
	 */
	public function attach($owner)
	{
		$this->owner = $owner;
		$owner->on ( Controller::EVENT_BEFORE_ACTION, [ 
			$this,
			'beforeFilter' 
		] );
	}
	
	/**
	 * @inheritdoc
	 */
	public function detach()
	{
		if ($this->owner) {
			$this->owner->off ( Controller::EVENT_BEFORE_ACTION, [ 
				$this,
				'beforeFilter' 
			] );
			$this->owner->off ( Controller::EVENT_AFTER_ACTION, [ 
				$this,
				'afterFilter' 
			] );
			$this->owner = null;
		}
	}
	
	/**
	 *
	 * @param ActionEvent $event
	 */
	public function beforeFilter($event)
	{
		if (! $this->isActive ( $event->action )) {
			return;
		}
		
		$event->isValid = $this->beforeAction ( $event->action );
		if ($event->isValid) {
			// call afterFilter only if beforeFilter succeeds
			// beforeFilter and afterFilter should be properly nested
			$this->owner->on ( Controller::EVENT_AFTER_ACTION, [ 
				$this,
				'afterFilter' 
			], null, false );
		} else {
			$event->handled = true;
		}
	}
	
	/**
	 *
	 * @param ActionEvent $event
	 */
	public function afterFilter($event)
	{
		$event->result = $this->afterAction ( $event->action, $event->result );
		$this->owner->off ( Controller::EVENT_AFTER_ACTION, [ 
			$this,
			'afterFilter' 
		] );
	}
	
	/**
	 * Action前置执行
	 * You may override this method to do last-minute preparation for the action.
	 *
	 * @param Action $action the action to be executed.
	 * @return boolean whether the action should continue to be executed.
	 */
	public function beforeAction($action)
	{
		return true;
	}
	
	/**
	 * Action后置执行
	 * You may override this method to do some postprocessing for the action.
	 *
	 * @param Action $action the action just executed.
	 * @param mixed $result the action execution result
	 * @return mixed the processed action result.
	 */
	public function afterAction($action, $result)
	{
		return $result;
	}
	
	/**
	 * 该操作的过滤器是否是活动的
	 *
	 * @param Action $action the action being filtered
	 * @return boolean whether the filer is active for the given action.
	 */
	protected function isActive($action)
	{
		if ($this->owner instanceof Module) {
			// convert action uniqueId into an ID relative to the module
			$mid = $this->owner->getUniqueId ();
			$id = $action->getUniqueId ();
			if ($mid !== '' && strpos ( $id, $mid ) === 0) {
				$id = substr ( $id, strlen ( $mid ) + 1 );
			}
		} else {
			$id = $action->id;
		}
		return ! in_array ( $id, $this->except, true ) && (empty ( $this->only ) || in_array ( $id, $this->only, true ));
	}
}
