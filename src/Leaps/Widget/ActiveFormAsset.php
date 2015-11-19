<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace Leaps\Widgets;

use Leaps\Web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActiveFormAsset extends AssetBundle
{
    public $sourcePath = '@Leaps/Asset';
    public $js = [
        'leaps.activeForm.js',
    ];
    public $depends = [
        'Leaps\Web\LeapsAsset',
    ];
}
