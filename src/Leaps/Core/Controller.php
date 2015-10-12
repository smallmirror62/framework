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

class Controller extends Base{

	/**
	 * 控制器ID
	 * @var string
	 */
	public $id;

	/**
	 * 该控制器所属模块
	 * @var Module $module
	 */
	public $module;

	/**
	 * 默认操作
	 * @var string
	 */
	public $defaultAction = 'index';

	/**
	 * 控制器视图，false为关闭
	 * @var string|boolean 控制器视图
	 */
	public $layout;

	/**
	 * 目前正在执行的Action
	 * @var Action This property will be set by [[run()]] when it is called by [[Application]] to run an action.
	 */
	public $action;

	/**
     * @param string $id 控制器ID
     * @param Module $module 该控制器所属模块
     * @param array $config 用来初始化对象属性的数组
     */
    public function __construct($id, $module, $config = [])
    {
        $this->id = $id;
        $this->module = $module;
        parent::__construct($config);
    }


    /**
     * Runs an action within this controller with the specified action ID and parameters.
     * If the action ID is empty, the method will use [[defaultAction]].
     * @param string $id the ID of the action to be executed.
     * @param array $params the parameters (name-value pairs) to be passed to the action.
     * @return mixed the result of the action.
     * @throws InvalidRouteException if the requested action ID cannot be resolved into an action successfully.
     * @see createAction()
     */
    public function runAction($id, $params = [])
    {
    	$action = $this->createAction($id);
    	if ($action === null) {
    		throw new InvalidRouteException('Unable to resolve the request: ' . $this->getUniqueId() . '/' . $id);
    	}

    	Kernel::trace("Route to run: " . $action->getUniqueId(), __METHOD__);

    	if (Kernel::app()->requestedAction === null) {
    		Kernel::app()->requestedAction = $action;
    	}

    	$oldAction = $this->action;
    	$this->action = $action;

    	$modules = [];
    	$runAction = true;

    	// call beforeAction on modules
    	foreach ($this->getModules() as $module) {
    		if ($module->beforeAction($action)) {
    			array_unshift($modules, $module);
    		} else {
    			$runAction = false;
    			break;
    		}
    	}

    	$result = null;

    	if ($runAction && $this->beforeAction($action)) {
    		// run the action
    		$result = $action->runWithParams($params);

    		$result = $this->afterAction($action, $result);

    		// call afterAction on modules
    		foreach ($modules as $module) {
    			/* @var $module Module */
    			$result = $module->afterAction($action, $result);
    		}
    	}

    	$this->action = $oldAction;

    	return $result;
    }

    /**
     * 执行指定的路由请求
     * The route can be either an ID of an action within this controller or a complete route consisting
     * of module IDs, controller ID and action ID. If the route starts with a slash '/', the parsing of
     * the route will start from the application; otherwise, it will start from the parent module of this controller.
     * @param string $route the route to be handled, e.g., 'view', 'comment/view', '/admin/comment/view'.
     * @param array $params the parameters to be passed to the action.
     * @return mixed the result of the action.
     * @see runAction()
     */
    public function run($route, $params = [])
    {
    	$pos = strpos($route, '/');
    	if ($pos === false) {
    		return $this->runAction($route, $params);
    	} elseif ($pos > 0) {
    		return $this->module->runAction($route, $params);
    	} else {
    		return Kernel::app()->runAction(ltrim($route, '/'), $params);
    	}
    }

    /**
     * 绑定参数到操作
     * This method is invoked by [[Action]] when it begins to run with the given parameters.
     * @param Action $action the action to be bound with parameters.
     * @param array $params the parameters to be bound to the action.
     * @return array the valid parameters that the action can run with.
     */
    public function bindActionParams($action, $params)
    {
    	return [];
    }

    /**
     * 创建一个操作
     * it will use the configuration declared there to create the action object.
     * If not, it will look for a controller method whose name is in the format of `actionXyz`
     * where `Xyz` stands for the action ID. If found, an [[InlineAction]] representing that
     * method will be created and returned.
     * @param string $id 操作的ID
     * @return Action the newly created action instance. Null if the ID doesn't resolve into any action.
     */
    public function createAction($id)
    {
    	if ($id === '') {
    		$id = $this->defaultAction;
    	}
    	if (preg_match('/^[a-z0-9\\-_]+$/', $id) && strpos($id, '--') === false && trim($id, '-') === $id) {
    		$methodName = str_replace(' ', '', ucwords(implode(' ', explode('-', $id)))).'Action';
    		if (method_exists($this, $methodName)) {
    			$method = new \ReflectionMethod($this, $methodName);
    			if ($method->isPublic() && $method->getName() === $methodName) {
    				return new InlineAction($id, $this, $methodName);
    			}
    		}
    	}
    	return null;
    }
}