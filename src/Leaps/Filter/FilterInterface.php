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
 * Leaps\FilterInterface
 *
 * Interface for Leaps\Filter\Filter
 */
interface FilterInterface
{

	/**
	 * Adds a user-defined filter
	 */
	public function add($name, $handler);

	/**
	 * Sanizites a value with a specified single or set of filters
	 */
	public function sanitize($value, $filters);

	/**
	 * Return the user-defined filters in the instance
	 */
	public function getFilters();
}
