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
namespace Leaps\Core;

/**
 * 使用错误的方式调用方法引起的异常。
 *
 * @author Tongle Xu <xutongle@gmail.com>
 * @since 4.0
 */
class InvalidCallException extends Exception
{
	/**
	 * 返回用户友好的异常名称
	 *
	 * @return string
	 */
	public function getName()
	{
		return 'Invalid Call';
	}
}