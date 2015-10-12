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
require (__DIR__ . '/src/Leaps/Kernel.php');
class Leaps extends \Leaps\Kernel
{
}

spl_autoload_register ( [
		'Leaps',
		'autoload'
], true, true );
Leaps::$_classMap = require (__DIR__ . '/classes.php');