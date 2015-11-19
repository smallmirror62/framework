<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace Leaps\Validator;

use Leaps\Web\AssetBundle;

/**
 * This asset bundle provides the javascript files for client validation.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ValidationAsset extends AssetBundle
{
    public $sourcePath = '@Leaps/Asset';
    public $js = [
        'leaps.validation.js',
    ];
    public $depends = [
        'Leaps\Web\LeapsAsset',
    ];
}
