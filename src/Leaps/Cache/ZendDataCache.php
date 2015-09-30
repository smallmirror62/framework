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
 * ZendDataCache provides Zend data caching in terms of an application component.
 */
class ZendDataCache extends Adapter
{
	/**
	 * (non-PHPdoc)
	 * @see \Leaps\Cache\Adapter::getValue()
	 */
	protected function getValue($key)
	{
		$result = zend_shm_cache_fetch($key);
		return $result === null ? false : $result;
	}

	/**
	 * (non-PHPdoc)
	 * @see \Leaps\Cache\Adapter::setValue()
	 */
	protected function setValue($key, $value, $duration)
	{
		return zend_shm_cache_store($key, $value, $duration);
	}

	/**
	 * (non-PHPdoc)
	 * @see \Leaps\Cache\Adapter::addValue()
	 */
	protected function addValue($key, $value, $duration)
	{
		return zend_shm_cache_fetch($key) === null ? $this->setValue($key, $value, $duration) : false;
	}

	/**
	 * (non-PHPdoc)
	 * @see \Leaps\Cache\Adapter::deleteValue()
	 */
	protected function deleteValue($key)
	{
		return zend_shm_cache_delete($key);
	}

	/**
	 * (non-PHPdoc)
	 * @see \Leaps\Cache\Adapter::flushValues()
	 */
	protected function flushValues()
	{
		return zend_shm_cache_clear();
	}
}
