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
namespace Leaps\Filter;

/**
 * Leaps\Filter\Exception
 *
 * Exceptions thrown in Leaps\Filter\Filter will use this class
 *
 */
class Exception extends \Leaps\Core\Exception
{
	/**
	 * 返回用户友好的异常名称
	 *
	 * @return string
	 */
	public function getName()
	{
		return 'Filter Exception';
	}
}