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
 * Leaps\Filter\UserFilterInterface
 *
 * Interface for Leaps\Filter\Filter user-filters
 */
interface UserFilterInterface
{

	/**
	 * Filters a value
	 */
	public function filter($value);
}
