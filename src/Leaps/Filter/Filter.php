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
 * Leaps\Filter\Filter
 *
 * The Leaps\Filter\Filter component provides a set of commonly needed data filters. It provides
 * object oriented wrappers to the php filter extension. Also allows the developer to
 * define his/her own filters
 *
 * <code>
 * $filter = new \Leaps\Filter\Filter();
 * $filter->sanitize("some(one)@exa\\mple.com", "email"); // returns "someone@example.com"
 * $filter->sanitize("hello<<", "string"); // returns "hello"
 * $filter->sanitize("!100a019", "int"); // returns "100019"
 * $filter->sanitize("!100a019.01a", "float"); // returns "100019.01"
 * </code>
 */
class Filter implements FilterInterface
{
	protected $_filters;

	/**
	 * 添加用户自定义过滤
	 */
	public function add($name, $handler)
	{
		if (! is_object ( $handler )) {
			throw new Exception ( "Filter must be an object" );
		}
		$this->_filters [$name] = $handler;
		return $this;
	}

	/**
	 * Sanitizes a value with a specified single or set of filters
	 */
	public function sanitize($value, $filters, $noRecursive = false)
	{
		/**
		 * Apply an array of filters
		 */
		if (is_array ( $filters )) {
			if (! is_null ( $value )) {
				foreach ( $filters as $filter ) {
					/**
					 * If the value to filter is an array we apply the filters recursively
					 */
					if (is_array ( $value ) && ! $noRecursive) {
						$arrayValue = [ ];
						foreach ( $value as $itemKey => $itemValue ) {
							$arrayValue [$itemKey] = $this->_sanitize ( $itemValue, $filter );
						}
						$value = $arrayValue;
					} else {
						$value = $this->_sanitize ( $value, $filter );
					}
				}
			}
			return $value;
		}
		if (is_array ( $value ) && ! $noRecursive) {
			$sanitizedValue = [ ];
			foreach ( $value as $itemKey => $itemValue ) {
				$sanitizedValue [$itemKey] = $this->_sanitize ( $itemValue, $filters );
			}
			return $sanitizedValue;
		}
		return $this->_sanitize ( $value, $filters );
	}

	/**
	 * Internal sanitize wrapper to filter_var
	 */
	protected function _sanitize($value, $filter)
	{
		if (isset ( $this->_filters [$filter] )) {
			if ($this->_filters [$filter] instanceof \Closure) {
				return call_user_func_array ( $this->_filters [$filter], [ $value ] );
			}
			return $this->_filters [$filter]->filter ( $value );
		}

		switch ($filter) {
			case "email" :
				return filter_var ( str_replace ( "'", "", $value ), constant ( "FILTER_SANITIZE_EMAIL" ) );

			case "int" :
				return filter_var ( $value, FILTER_SANITIZE_NUMBER_INT );

			case "int!" :
				return intval ( $value );

			case "string" :
				return filter_var ( $value, FILTER_SANITIZE_STRING );

			case "float" :
				return filter_var ( $value, FILTER_SANITIZE_NUMBER_FLOAT, [ "flags" => FILTER_FLAG_ALLOW_FRACTION ] );

			case "float!" :
				return doubleval ( $value );

			case "alphanum" :
				return preg_replace ( "/[^A-Za-z0-9]/", "", $value );

			case "trim" :
				return trim ( $value );

			case "striptags" :
				return strip_tags ( $value );

			case "lower" :
				if (function_exists ( "mb_strtolower" )) {
					return mb_strtolower ( $value );
				}
				return strtolower ( $value );

			case "upper" :
				if (function_exists ( "mb_strtoupper" )) {
					return mb_strtoupper ( $value );
				}
				return strtoupper ( $value );

			default :
				throw new Exception ( "Sanitize filter '" . $filter . "' is not supported" );
		}
	}

	/**
	 * 返回用户注册的过滤器实例
	 */
	public function getFilters()
	{
		return $this->_filters;
	}
}
