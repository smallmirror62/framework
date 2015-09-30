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

class DummyCache extends Adapter
{
	/**
	 * (non-PHPdoc)
	 * @see \Leaps\Cache\Adapter::getValue()
	 */
	protected function getValue($key)
	{
		return false;
	}

	/**
	 * (non-PHPdoc)
	 * @see \Leaps\Cache\Adapter::setValue()
	 */
	protected function setValue($key, $value, $duration)
	{
		return true;
	}

	/**
	 * (non-PHPdoc)
	 * @see \Leaps\Cache\Adapter::addValue()
	 */
	protected function addValue($key, $value, $duration)
	{
		return true;
	}

	/**
	 * (non-PHPdoc)
	 * @see \Leaps\Cache\Adapter::deleteValue()
	 */
	protected function deleteValue($key)
	{
		return true;
	}

	/**
	 * (non-PHPdoc)
	 * @see \Leaps\Cache\Adapter::flushValues()
	 */
	protected function flushValues()
	{
		return true;
	}
}