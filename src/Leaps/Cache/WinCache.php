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

/**
 * WinCache provides Windows Cache caching in terms of an application component.
 *
 */
class WinCache extends Adapter
{
	/**
	 * (non-PHPdoc)
	 * @see \Leaps\Cache\Adapter::exists()
	 */
	public function exists($key)
	{
		$key = $this->buildKey($key);
		return wincache_ucache_exists($key);
	}

	/**
	 * (non-PHPdoc)
	 * @see \Leaps\Cache\Adapter::getValue()
	 */
	protected function getValue($key)
	{
		return wincache_ucache_get($key);
	}

	/**
	 * (non-PHPdoc)
	 * @see \Leaps\Cache\Adapter::getValues()
	 */
	protected function getValues($keys)
	{
		return wincache_ucache_get($keys);
	}

	/**
	 * (non-PHPdoc)
	 * @see \Leaps\Cache\Adapter::setValue()
	 */
	protected function setValue($key, $value, $duration)
	{
		return wincache_ucache_set($key, $value, $duration);
	}

	/**
	 * (non-PHPdoc)
	 * @see \Leaps\Cache\Adapter::setValues()
	 */
	protected function setValues($data, $duration)
	{
		return wincache_ucache_set($data, null, $duration);
	}

	/**
	 * (non-PHPdoc)
	 * @see \Leaps\Cache\Adapter::addValue()
	 */
	protected function addValue($key, $value, $duration)
	{
		return wincache_ucache_add($key, $value, $duration);
	}

	/**
	 * (non-PHPdoc)
	 * @see \Leaps\Cache\Adapter::addValues()
	 */
	protected function addValues($data, $duration)
	{
		return wincache_ucache_add($data, null, $duration);
	}

	/**
	 * (non-PHPdoc)
	 * @see \Leaps\Cache\Adapter::deleteValue()
	 */
	protected function deleteValue($key)
	{
		return wincache_ucache_delete($key);
	}

	/**
	 * (non-PHPdoc)
	 * @see \Leaps\Cache\Adapter::flushValues()
	 */
	protected function flushValues()
	{
		return wincache_ucache_clear();
	}
}
