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
namespace Leaps\Base;

/**
 * InvalidCallException 使用一个错误的方式调用方法
 */
class InvalidCallException extends \BadMethodCallException
{
	/**
	 *
	 * @return string the user-friendly name of this exception
	 */
	public function getName()
	{
		return 'Invalid Call';
	}
}