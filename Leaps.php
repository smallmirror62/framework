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
/**
 * 加载Leaps核心
 */
require (__DIR__ . '/src/Leaps/Kernel.php');

/**
 * Leaps is a helper class serving common framework functionalities.
 *
 * It extends from [[\Leaps\Kernel]] which provides the actual implementation.
 * By writing your own Leaps class, you can customize some functionalities of [[\Leaps\Kernel]].
 */
class Leaps extends \Leaps\Kernel
{
}

spl_autoload_register ( [ 
	'Leaps',
	'autoload' 
], true, true );
Leaps::$classMap = require (__DIR__ . '/classes.php');
Leaps::$container = new Leaps\Di\Container ();