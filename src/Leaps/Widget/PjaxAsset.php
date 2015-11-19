<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace Leaps\Widgets;

use Leaps\Web\AssetBundle;

/**
 * This asset bundle provides the javascript files required by [[Pjax]] widget.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class PjaxAsset extends AssetBundle
{
    public $sourcePath = '@bower/leaps-pjax';
    public $js = [
        'jquery.pjax.js',
    ];
    public $depends = [
        'Leaps\Web\LeapsAsset',
    ];
}
