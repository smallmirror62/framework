<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
namespace Leaps\Web;

/**
 * This asset bundle provides the base javascript files for the Yii Framework.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class LeapsAsset extends AssetBundle
{
	public $sourcePath = '@Leaps/Asset';
	public $js = [ 
		'leaps.js' 
	];
	public $depends = [ 
		'Leaps\Web\JqueryAsset' 
	];
}
