<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2015 TintSoft
 * @license http://www.tintsoft.com/license/
 */

namespace Leaps\Grid;

use Leaps\Web\AssetBundle;

/**
 * This asset bundle provides the javascript files for the [[GridView]] widget.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class GridViewAsset extends AssetBundle
{
    public $sourcePath = '@Leaps/Asset';
    public $js = [
        'leaps.gridView.js',
    ];
    public $depends = [
        'Leaps\Web\LeapsAsset',
    ];
}
