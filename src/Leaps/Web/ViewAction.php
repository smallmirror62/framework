<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2015 TintSoft
 * @license http://www.tintsoft.com/license/
 */

namespace Leaps\Web;

use Leaps;
use Leaps\Base\Action;
use Leaps\Base\InvalidParamException;

/**
 * ViewAction represents an action that displays a view according to a user-specified parameter.
 *
 * By default, the view being displayed is specified via the `view` GET parameter.
 * The name of the GET parameter can be customized via [[viewParam]].
 *
 * Users specify a view in the format of `path/to/view`, which translates to the view name
 * `ViewPrefix/path/to/view` where `ViewPrefix` is given by [[viewPrefix]]. The view will then
 * be rendered by the [[\Leaps\Base\Controller::render()|render()]] method of the currently active controller.
 *
 * Note that the user-specified view name must start with a word character and can only contain
 * word characters, forward slashes, dots and dashes.
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ViewAction extends Action
{
    /**
     * @var string the name of the GET parameter that contains the requested view name.
     */
    public $viewParam = 'view';
    /**
     * @var string the name of the default view when [[\Leaps\Web\ViewAction::$viewParam]] GET parameter is not provided
     * by user. Defaults to 'index'. This should be in the format of 'path/to/view', similar to that given in the
     * GET parameter.
     * @see \Leaps\Web\ViewAction::$viewPrefix
     */
    public $defaultView = 'index';
    /**
     * @var string a string to be prefixed to the user-specified view name to form a complete view name.
     * For example, if a user requests for `tutorial/chap1`, the corresponding view name will
     * be `pages/tutorial/chap1`, assuming the prefix is `pages`.
     * The actual view file is determined by [[\Leaps\Base\View::findViewFile()]].
     * @see \Leaps\Base\View::findViewFile()
     */
    public $viewPrefix = 'pages';
    /**
     * @var mixed the name of the layout to be applied to the requested view.
     * This will be assigned to [[\Leaps\Base\Controller::$layout]] before the view is rendered.
     * Defaults to null, meaning the controller's layout will be used.
     * If false, no layout will be applied.
     */
    public $layout;


    /**
     * Runs the action.
     * This method displays the view requested by the user.
     * @throws NotFoundHttpException if the view file cannot be found
     */
    public function run()
    {
        $viewName = $this->resolveViewName();

        $controllerLayout = null;
        if ($this->layout !== null) {
            $controllerLayout = $this->controller->layout;
            $this->controller->layout = $this->layout;
        }

        try {
            $output = $this->render($viewName);

            if ($controllerLayout) {
                $this->controller->layout = $controllerLayout;
            }

        } catch (InvalidParamException $e) {

            if ($controllerLayout) {
                $this->controller->layout = $controllerLayout;
            }

            if (LEAPS_DEBUG) {
                throw new NotFoundHttpException($e->getMessage());
            } else {
                throw new NotFoundHttpException(
                    Leaps::t('leaps', 'The requested view "{name}" was not found.', ['name' => $viewName])
                );
            }
        }

        return $output;
    }

    /**
     * Renders a view
     *
     * @param string $viewName view name
     * @return string result of the rendering
     */
    protected function render($viewName)
    {
        return $this->controller->render($viewName);
    }

    /**
     * Resolves the view name currently being requested.
     *
     * @return string the resolved view name
     * @throws NotFoundHttpException if the specified view name is invalid
     */
    protected function resolveViewName()
    {
        $viewName = Leaps::$app->request->get($this->viewParam, $this->defaultView);

        if (!is_string($viewName) || !preg_match('~^\w(?:(?!\/\.{0,2}\/)[\w\/\-\.])*$~', $viewName)) {
            if (LEAPS_DEBUG) {
                throw new NotFoundHttpException("The requested view \"$viewName\" must start with a word character, must not contain /../ or /./, can contain only word characters, forward slashes, dots and dashes.");
            } else {
                throw new NotFoundHttpException(Leaps::t('leaps', 'The requested view "{name}" was not found.', ['name' => $viewName]));
            }
        }

        return empty($this->viewPrefix) ? $viewName : $this->viewPrefix . '/' . $viewName;
    }
}
