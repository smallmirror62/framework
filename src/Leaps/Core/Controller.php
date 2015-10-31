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

class Controller extends Base implements ViewContextInterface
{

	/**
	 * 控制器ID
	 *
	 * @var string
	 */
	public $id;

	/**
	 * 该控制器所属模块
	 *
	 * @var Module $module
	 */
	public $module;

	/**
	 * 默认操作
	 *
	 * @var string
	 */
	public $defaultAction = 'index';

	/**
	 * 控制器视图，false为关闭
	 *
	 * @var string|boolean 控制器视图
	 */
	public $layout;

	/**
	 * 目前正在执行的Action
	 *
	 * @var Action This property will be set by [[run()]] when it is called by [[Application]] to run an action.
	 */
	public $action;

	/**
	 * 可以用来渲染视图或视图文件的视图对象
	 *
	 * @var View
	 */
	private $_view;

	/**
	 * 构造方法
	 *
	 * @param string $id 控制器ID
	 * @param Module $module 该控制器所属模块
	 * @param array $config 用来初始化对象属性的数组
	 */
	public function __construct($id, $module, $config = [])
	{
		$this->id = $id;
		$this->module = $module;
		parent::__construct ( $config );
	}

	/**
	 * 在该控制器执行指定的操作
	 *
	 * @param string $id 操作ID
	 * @param array $params 绑定到该操作的参数
	 * @return mixed 操作执行结果
	 * @throws InvalidRouteException 如果该请求操作不能成功解析
	 * @see createAction()
	 */
	public function runAction($id, $params = [])
	{
		$action = $this->createAction ( $id );
		if ($action === null) {
			throw new InvalidRouteException ( 'Unable to resolve the request: ' . $this->getUniqueId () . '/' . $id );
		}
		Leaps::trace ( "Route to run: " . $action->getUniqueId (), __METHOD__ );
		if (Leaps::app ()->requestedAction === null) {
			Leaps::app ()->requestedAction = $action;
		}
		$oldAction = $this->action;
		$this->action = $action;
		// run the action
		$result = $action->runWithParams ( $params );

		$this->action = $oldAction;
		return $result;
	}

	/**
	 * 执行指定的路由请求
	 * The route can be either an ID of an action within this controller or a complete route consisting
	 * of module IDs, controller ID and action ID.
	 * If the route starts with a slash '/', the parsing of
	 * the route will start from the application; otherwise, it will start from the parent module of this controller.
	 *
	 * @param string $route the route to be handled, e.g., 'view', 'comment/view', '/admin/comment/view'.
	 * @param array $params the parameters to be passed to the action.
	 * @return mixed the result of the action.
	 * @see runAction()
	 */
	public function run($route, $params = [])
	{
		$pos = strpos ( $route, '/' );
		if ($pos === false) {
			return $this->runAction ( $route, $params );
		} elseif ($pos > 0) {
			return $this->module->runAction ( $route, $params );
		} else {
			return Leaps::app ()->runAction ( ltrim ( $route, '/' ), $params );
		}
	}

	/**
	 * 绑定参数到操作
	 * This method is invoked by [[Action]] when it begins to run with the given parameters.
	 *
	 * @param Action $action the action to be bound with parameters.
	 * @param array $params the parameters to be bound to the action.
	 * @return array the valid parameters that the action can run with.
	 */
	public function bindActionParams($action, $params)
	{
		return [ ];
	}

	/**
	 * 创建一个操作
	 * it will use the configuration declared there to create the action object.
	 * If not, it will look for a controller method whose name is in the format of `actionXyz`
	 * where `Xyz` stands for the action ID. If found, an [[InlineAction]] representing that
	 * method will be created and returned.
	 *
	 * @param string $id 操作的ID
	 * @return Action the newly created action instance. Null if the ID doesn't resolve into any action.
	 */
	public function createAction($id)
	{
		if ($id === '') {
			$id = $this->defaultAction;
		}
		if (preg_match ( '/^[a-z0-9\\-_]+$/', $id ) && strpos ( $id, '--' ) === false && trim ( $id, '-' ) === $id) {
			$methodName = str_replace ( ' ', '', implode ( ' ', explode ( '-', $id ) ) ) . 'Action';
			if (method_exists ( $this, $methodName )) {
				$method = new \ReflectionMethod ( $this, $methodName );
				if ($method->isPublic () && $method->getName () === $methodName) {
					return new InlineAction ( $id, $this, $methodName );
				}
			}
		}
		return null;
	}

	/**
	 * 返回该控制器所有的父模块
	 *
	 * @return Module[] 该控制器所有的父模块
	 */
	public function getModules()
	{
		$modules = [ $this->module ];
		$module = $this->module;
		while ( $module->module !== null ) {
			array_unshift ( $modules, $module->module );
			$module = $module->module;
		}
		return $modules;
	}

	/**
	 *
	 * @return string the controller ID that is prefixed with the module ID (if any).
	 */
	public function getUniqueId()
	{
		return $this->module instanceof Application ? $this->id : $this->module->getUniqueId () . '/' . $this->id;
	}

	/**
	 * 返回该请求的路由
	 *
	 * @return string the route (module ID, controller ID and action ID) of the current request.
	 */
	public function getRoute()
	{
		return $this->action !== null ? $this->action->getUniqueId () : $this->getUniqueId ();
	}

	/**
	 * Renders a view and applies layout if available.
	 *
	 * The view to be rendered can be specified in one of the following formats:
	 *
	 * - path alias (e.g. "@app/views/site/index");
	 * - absolute path within application (e.g. "//site/index"): the view name starts with double slashes.
	 * The actual view file will be looked for under the [[Application::viewPath|view path]] of the application.
	 * - absolute path within module (e.g. "/site/index"): the view name starts with a single slash.
	 * The actual view file will be looked for under the [[Module::viewPath|view path]] of [[module]].
	 * - relative path (e.g. "index"): the actual view file will be looked for under [[viewPath]].
	 *
	 * To determine which layout should be applied, the following two steps are conducted:
	 *
	 * 1. In the first step, it determines the layout name and the context module:
	 *
	 * - If [[layout]] is specified as a string, use it as the layout name and [[module]] as the context module;
	 * - If [[layout]] is null, search through all ancestor modules of this controller and find the first
	 * module whose [[Module::layout|layout]] is not null. The layout and the corresponding module
	 * are used as the layout name and the context module, respectively. If such a module is not found
	 * or the corresponding layout is not a string, it will return false, meaning no applicable layout.
	 *
	 * 2. In the second step, it determines the actual layout file according to the previously found layout name
	 * and context module. The layout name can be:
	 *
	 * - a path alias (e.g. "@app/views/layouts/main");
	 * - an absolute path (e.g. "/main"): the layout name starts with a slash. The actual layout file will be
	 * looked for under the [[Application::layoutPath|layout path]] of the application;
	 * - a relative path (e.g. "main"): the actual layout file will be looked for under the
	 * [[Module::layoutPath|layout path]] of the context module.
	 *
	 * If the layout name does not contain a file extension, it will use the default one `.php`.
	 *
	 * @param string $view the view name.
	 * @param array $params the parameters (name-value pairs) that should be made available in the view.
	 *        These parameters will not be available in the layout.
	 * @return string the rendering result.
	 * @throws InvalidParamException if the view file or the layout file does not exist.
	 */
	public function render($view, $params = [])
	{
		$content = $this->getView ()->render ( $view, $params, $this );
		return $this->renderContent ( $content );
	}

	/**
	 * 使用布局来渲染字符串
	 *
	 * @param string $content the static string being rendered
	 * @return string the rendering result of the layout with the given static string as the `$content` variable.
	 *         If the layout is disabled, the string will be returned back.
	 * @since 2.0.1
	 */
	public function renderContent($content)
	{
		$layoutFile = $this->findLayoutFile ( $this->getView () );
		if ($layoutFile !== false) {
			return $this->getView ()->renderFile ( $layoutFile, [ 'content' => $content ], $this );
		} else {
			return $content;
		}
	}

	/**
	 * 不使用布局呈现视图
	 *
	 * @param string $view the view name. Please refer to [[render()]] on how to specify a view name.
	 * @param array $params the parameters (name-value pairs) that should be made available in the view.
	 * @return string the rendering result.
	 * @throws InvalidParamException if the view file does not exist.
	 */
	public function renderPartial($view, $params = [])
	{
		return $this->getView ()->render ( $view, $params, $this );
	}

	/**
	 * 渲染视图文件
	 *
	 * @param string $file the view file to be rendered. This can be either a file path or a path alias.
	 * @param array $params the parameters (name-value pairs) that should be made available in the view.
	 * @return string the rendering result.
	 * @throws InvalidParamException if the view file does not exist.
	 */
	public function renderFile($file, $params = [])
	{
		return $this->getView ()->renderFile ( $file, $params, $this );
	}

	/**
	 * 返回可用于渲染视图或视图文件的视图对象
	 * The [[render()]], [[renderPartial()]] and [[renderFile()]] methods will use
	 * this view object to implement the actual view rendering.
	 * If not set, it will default to the "view" application component.
	 *
	 * @return View|\Leaps\Web\View the view object that can be used to render views or view files.
	 */
	public function getView()
	{
		if ($this->_view === null) {
			$this->_view = Leaps::$app->getView ();
		}
		return $this->_view;
	}

	/**
	 * 设置该控制器可用于渲染视图或视图文件的视图对象
	 *
	 * @param View|\Leaps\Web\View $view the view object that can be used to render views or view files.
	 */
	public function setView($view)
	{
		$this->_view = $view;
	}

	/**
	 * 返回该控制器的视图文件夹
	 * The default implementation returns the directory named as controller [[id]] under the [[module]]'s
	 * [[viewPath]] directory.
	 *
	 * @return string the directory containing the view files for this controller.
	 */
	public function getViewPath()
	{
		return $this->module->getViewPath () . DIRECTORY_SEPARATOR . ucwords($this->id);
	}

	/**
	 * 查找应用布局文件
	 *
	 * @param View $view the view object to render the layout file.
	 * @return string|boolean the layout file path, or false if layout is not needed.
	 *         Please refer to [[render()]] on how to specify this parameter.
	 * @throws InvalidParamException if an invalid path alias is used to specify the layout.
	 */
	public function findLayoutFile($view)
	{
		$module = $this->module;
		if (is_string ( $this->layout )) {
			$layout = $this->layout;
		} elseif ($this->layout === null) {
			while ( $module !== null && $module->layout === null ) {
				$module = $module->module;
			}
			if ($module !== null && is_string ( $module->layout )) {
				$layout = $module->layout;
			}
		}
		if (! isset ( $layout )) {
			return false;
		}
		if (strncmp ( $layout, '@', 1 ) === 0) {
			$file = Leaps::getAlias ( $layout );
		} elseif (strncmp ( $layout, '/', 1 ) === 0) {
			$file = Leaps::$app->getLayoutPath () . DIRECTORY_SEPARATOR . substr ( $layout, 1 );
		} else {
			$file = $module->getLayoutPath () . DIRECTORY_SEPARATOR . $layout;
		}
		if (pathinfo ( $file, PATHINFO_EXTENSION ) !== '') {
			return $file;
		}
		$path = $file . '.' . $view->defaultExtension;
		if ($view->defaultExtension !== 'php' && ! is_file ( $path )) {
			$path = $file . '.php';
		}
		return $path;
	}
}