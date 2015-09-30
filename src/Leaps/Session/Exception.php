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
namespace Leaps\Session;

/**
 * Leaps\Session\Exception
 *
 * Exceptions thrown in Leaps\Session will use this class
 *
 */
class Exception extends \Leaps\Core\Exception
{
	public function getName(){
		return 'Session Exception';
	}
}