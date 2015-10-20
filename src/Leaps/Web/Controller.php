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
namespace Leaps\Web;

use Leaps;
use Leaps\Core\InlineAction;

class Controller extends \Leaps\Core\Controller
{
	/**
	 * 操作参数
	 * @var array
	 */
	public $actionParams = [ ];

	/**
	 * 渲染Ajax请求的视图
	 *
	 * @param string $view the view name. Please refer to [[render()]] on how to specify a view name.
	 * @param array $params the parameters (name-value pairs) that should be made available in the view.
	 * @return string the rendering result.
	 */
	public function renderAjax($view, $params = [])
	{
		return $this->getView ()->renderAjax ( $view, $params, $this );
	}

	/**
	 * 绑定参数到操作
	 *
	 * @param \Leaps\Core\Action $action action实例
	 * @param array $params 参数
	 * @return array the valid parameters that the action can run with.
	 * @throws BadRequestHttpException if there are missing or invalid parameters.
	 */
	public function bindActionParams($action, $params)
	{
		if ($action instanceof InlineAction) {
			$method = new \ReflectionMethod ( $this, $action->actionMethod );
		} else {
			$method = new \ReflectionMethod ( $action, 'run' );
		}
		$args = [ ];
		$missing = [ ];
		$actionParams = [ ];
		foreach ( $method->getParameters () as $param ) {
			$name = $param->getName ();
			if (array_key_exists ( $name, $params )) {
				if ($param->isArray ()) {
					$args [] = $actionParams [$name] = ( array ) $params [$name];
				} elseif (! is_array ( $params [$name] )) {
					$args [] = $actionParams [$name] = $params [$name];
				} else {
					throw new BadRequestHttpException ( 'Invalid data received for parameter "' . $name . '".' );
				}
				unset ( $params [$name] );
			} elseif ($param->isDefaultValueAvailable ()) {
				$args [] = $actionParams [$name] = $param->getDefaultValue ();
			} else {
				$missing [] = $name;
			}
		}
		if (! empty ( $missing )) {
			throw new BadRequestHttpException ( 'Missing required parameters: ' . implode ( ', ', $missing ) );
		}
		$this->actionParams = $actionParams;
		return $args;
	}

	/**
	 * Redirects the browser to the specified URL.
	 * This method is a shortcut to [[Response::redirect()]].
	 *
	 * You can use it in an action by returning the [[Response]] directly:
	 *
	 * ```php
	 * // stop executing this action and redirect to login page
	 * return $this->redirect(['login']);
	 * ```
	 *
	 * @param string|array $url the URL to be redirected to. This can be in one of the following formats:
	 *
	 *        - a string representing a URL (e.g. "http://example.com")
	 *        - a string representing a URL alias (e.g. "@example.com")
	 *        - an array in the format of `[$route, ...name-value pairs...]` (e.g. `['site/index', 'ref' => 1]`)
	 *        [[Url::to()]] will be used to convert the array into a URL.
	 *
	 *        Any relative URL will be converted into an absolute one by prepending it with the host info
	 *        of the current request.
	 *
	 * @param integer $statusCode the HTTP status code. Defaults to 302.
	 *        See <http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html>
	 *        for details about HTTP status code
	 * @return Response the current response object
	 */
	public function redirect($url, $statusCode = 302)
	{
		return Leaps::$app->getResponse ()->redirect ( Url::to ( $url ), $statusCode );
	}

	/**
	 * Redirects the browser to the home page.
	 *
	 * You can use this method in an action by returning the [[Response]] directly:
	 *
	 * ```php
	 * // stop executing this action and redirect to home page
	 * return $this->goHome();
	 * ```
	 *
	 * @return Response the current response object
	 */
	public function goHome()
	{
		return Leaps::$app->getResponse ()->redirect ( Leaps::$app->getHomeUrl () );
	}

	/**
	 * Redirects the browser to the last visited page.
	 *
	 * You can use this method in an action by returning the [[Response]] directly:
	 *
	 * ```php
	 * // stop executing this action and redirect to last visited page
	 * return $this->goBack();
	 * ```
	 *
	 * For this function to work you have to [[User::setReturnUrl()|set the return URL]] in appropriate places before.
	 *
	 * @param string|array $defaultUrl the default return URL in case it was not set previously.
	 *        If this is null and the return URL was not set previously, [[Application::homeUrl]] will be redirected to.
	 *        Please refer to [[User::setReturnUrl()]] on accepted format of the URL.
	 * @return Response the current response object
	 * @see User::getReturnUrl()
	 */
	public function goBack($defaultUrl = null)
	{
		return Leaps::$app->getResponse ()->redirect ( Leaps::$app->getUser ()->getReturnUrl ( $defaultUrl ) );
	}

	/**
	 * Refreshes the current page.
	 * This method is a shortcut to [[Response::refresh()]].
	 *
	 * You can use it in an action by returning the [[Response]] directly:
	 *
	 * ```php
	 * // stop executing this action and refresh the current page
	 * return $this->refresh();
	 * ```
	 *
	 * @param string $anchor the anchor that should be appended to the redirection URL.
	 *        Defaults to empty. Make sure the anchor starts with '#' if you want to specify it.
	 * @return Response the response object itself
	 */
	public function refresh($anchor = '')
	{
		return Leaps::$app->getResponse ()->redirect ( Leaps::$app->getRequest ()->getUrl () . $anchor );
	}
}
