<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2015 TintSoft
 * @license http://www.tintsoft.com/license/
 */

namespace Leaps\Base;

/**
 * ViewRenderer is the base class for view renderer classes.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class ViewRenderer extends Service
{
    /**
     * Renders a view file.
     *
     * This method is invoked by [[View]] whenever it tries to render a view.
     * Child classes must implement this method to render the given view file.
     *
     * @param View $view the view object used for rendering the file.
     * @param string $file the view file.
     * @param array $params the parameters to be passed to the view file.
     * @return string the rendering result
     */
    abstract public function render($view, $file, $params);
}
