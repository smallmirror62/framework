<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2015 TintSoft
 * @license http://www.tintsoft.com/license/
 */

namespace Leaps\Validator;

use Leaps\Web\AssetBundle;

/**
 * This asset bundle provides the javascript files needed for the [[EmailValidator]]s client validation.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class PunycodeAsset extends AssetBundle
{
    public $sourcePath = '@bower/punycode';
    public $js = [
        'punycode.js',
    ];
}
