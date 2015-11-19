<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace Leaps\Captcha;

use Leaps\Web\AssetBundle;

/**
 * This asset bundle provides the javascript files needed for the [[Captcha]] widget.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class CaptchaAsset extends AssetBundle
{
    public $sourcePath = '@Leaps/Asset';
    public $js = [
        'leaps.captcha.js',
    ];
    public $depends = [
        'Leaps\Web\LeapsAsset',
    ];
}
