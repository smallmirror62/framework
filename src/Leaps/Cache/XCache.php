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
namespace Leaps\Cache;

class XCache extends Adapter
{
	/**
	 * (non-PHPdoc)
	 * @see \Leaps\Cache\Adapter::exists()
	 */
	public function exists($key)
	{
		$key = $this->buildKey ( $key );
		return xcache_isset ( $key );
	}

	/**
	 * (non-PHPdoc)
	 * @see \Leaps\Cache\Adapter::getValue()
	 */
	protected function getValue($key)
	{
		return xcache_isset ( $key ) ? xcache_get ( $key ) : false;
	}

	/**
	 * (non-PHPdoc)
	 * @see \Leaps\Cache\Adapter::setValue()
	 */
	protected function setValue($key, $value, $duration)
	{
		return xcache_set ( $key, $value, $duration );
	}

	/**
	 * (non-PHPdoc)
	 * @see \Leaps\Cache\Adapter::addValue()
	 */
	protected function addValue($key, $value, $duration)
	{
		return ! xcache_isset ( $key ) ? $this->setValue ( $key, $value, $duration ) : false;
	}

	/**
	 * (non-PHPdoc)
	 * @see \Leaps\Cache\Adapter::deleteValue()
	 */
	protected function deleteValue($key)
	{
		return xcache_unset ( $key );
	}

	/**
	 * (non-PHPdoc)
	 * @see \Leaps\Cache\Adapter::flushValues()
	 */
	protected function flushValues()
	{
		for($i = 0, $max = xcache_count ( XC_TYPE_VAR ); $i < $max; $i ++) {
			if (xcache_clear_cache ( XC_TYPE_VAR, $i ) === false) {
				return false;
			}
		}

		return true;
	}
}