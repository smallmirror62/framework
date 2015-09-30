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

use Leaps\Di\Injectable;

abstract class Adapter extends Injectable
{
	/**
	 * 设置缓存前缀
	 *
	 * @var string 缓存前缀
	 */
	public $keyPrefix;

	/**
	 * 编译缓存的Key
	 *
	 * @param mixed $key the key to be normalized
	 * @return string the generated cache key
	 */
	public function buildKey($key)
	{
		$key = (ctype_alnum ( $key ) && mb_strlen ( $key, '8bit' ) <= 32) ? $key : md5 ( $key );
		return $this->keyPrefix . $key;
	}

	/**
	 * 判断Key是否存在
	 *
	 * @param mixed $key a key identifying the cached value. This can be a simple string or
	 *        a complex data structure consisting of factors representing the key.
	 * @return boolean true if a value exists in cache, false if the value is not in the cache or expired.
	 */
	public function exists($key)
	{
		$key = $this->buildKey ( $key );
		$value = $this->getValue ( $key );
		return $value !== false;
	}

	/**
	 * 从缓存中获取Key
	 *
	 * @param mixed $key a key identifying the cached value. This can be a simple string or
	 *        a complex data structure consisting of factors representing the key.
	 * @return mixed the value stored in cache, false if the value is not in the cache, expired,
	 *         or the dependency associated with the cached data has changed.
	 */
	public function get($key)
	{
		$key = $this->buildKey ( $key );
		$value = $this->getValue ( $key );
		if ($value === false) {
			return $value;
		} else {
			return unserialize ( $value );
		}
		return false;
	}

	/**
	 * 存储Key到缓存
	 *
	 * @param mixed $key a key identifying the value to be cached. This can be a simple string or
	 *        a complex data structure consisting of factors representing the key.
	 * @param mixed $value the value to be cached
	 * @param integer $duration the number of seconds in which the cached value will expire. 0 means never expire.
	 * @return boolean whether the value is successfully stored into cache
	 */
	public function set($key, $value, $duration = 0)
	{
		$key = $this->buildKey ( $key );
		$value = serialize ( $value );
		return $this->setValue ( $key, $value, $duration );
	}

	/**
	 * 添加Key到缓存
	 *
	 * @param mixed $key a key identifying the value to be cached. This can be a simple string or
	 *        a complex data structure consisting of factors representing the key.
	 * @param mixed $value the value to be cached
	 * @param integer $duration the number of seconds in which the cached value will expire. 0 means never expire.
	 * @return boolean whether the value is successfully stored into cache
	 */
	public function add($key, $value, $duration = 0)
	{
		$value = serialize ( $value );
		$key = $this->buildKey ( $key );
		return $this->addValue ( $key, $value, $duration );
	}

	/**
	 * 从缓存中删除Key
	 *
	 * @param mixed $key a key identifying the value to be deleted from cache. This can be a simple string or
	 *        a complex data structure consisting of factors representing the key.
	 * @return boolean if no error happens during deletion
	 */
	public function delete($key)
	{
		$key = $this->buildKey ( $key );
		return $this->deleteValue ( $key );
	}

	/**
	 * 清空缓存
	 *
	 * @return boolean whether the flush operation was successful.
	 */
	public function flush()
	{
		return $this->flushValues ();
	}

	/**
	 * 从缓存中获取Key
	 *
	 * @param string $key a unique key identifying the cached value
	 * @return string|boolean stored in cache, false if the value is not in the cache or expired.
	 */
	abstract protected function getValue($key);

	/**
	 * 存储Key到缓存中
	 *
	 * @param string $key the key identifying the value to be cached
	 * @param string $value the value to be cached
	 * @param integer $duration the number of seconds in which the cached value will expire. 0 means never expire.
	 * @return boolean true if the value is successfully stored into cache, false otherwise
	 */
	abstract protected function setValue($key, $value, $duration);

	/**
	 * 添加Key到缓存中
	 *
	 * @param string $key the key identifying the value to be cached
	 * @param string $value the value to be cached
	 * @param integer $duration the number of seconds in which the cached value will expire. 0 means never expire.
	 * @return boolean true if the value is successfully stored into cache, false otherwise
	 */
	abstract protected function addValue($key, $value, $duration);

	/**
	 * 从存储中删除key
	 *
	 * @param string $key the key of the value to be deleted
	 * @return boolean if no error happens during deletion
	 */
	abstract protected function deleteValue($key);

	/**
	 * 清空缓存
	 *
	 * @return boolean whether the flush operation was successful.
	 */
	abstract protected function flushValues();

	/**
	 * 从缓存中批量获取Keys
	 *
	 * @param array $keys a list of keys identifying the cached values
	 * @return array a list of cached values indexed by the keys
	 */
	protected function getValues($keys)
	{
		$results = [ ];
		foreach ( $keys as $key ) {
			$results [$key] = $this->getValue ( $key );
		}
		return $results;
	}

	/**
	 * 批量存储到缓存中
	 * 默认调用setValue()多次设置值。
	 *
	 * @param array $data array where key corresponds to cache key while value is the value stored
	 * @param integer $duration the number of seconds in which the cached values will expire. 0 means never expire.
	 * @return array array of failed keys
	 */
	protected function setValues($data, $duration)
	{
		$failedKeys = [ ];
		foreach ( $data as $key => $value ) {
			if ($this->setValue ( $key, $value, $duration ) === false) {
				$failedKeys [] = $key;
			}
		}
		return $failedKeys;
	}

	/**
	 * 批量添加到缓存中
	 * 默认调用addvalue()多次添加值。
	 *
	 * @param array $data array where key corresponds to cache key while value is the value stored
	 * @param integer $duration the number of seconds in which the cached values will expire. 0 means never expire.
	 * @return array array of failed keys
	 */
	protected function addValues($data, $duration)
	{
		$failedKeys = [ ];
		foreach ( $data as $key => $value ) {
			if ($this->addValue ( $key, $value, $duration ) === false) {
				$failedKeys [] = $key;
			}
		}
		return $failedKeys;
	}

	/**
	 * 返回缓存中是否有Key存在
	 * 这个方法是由接口ArrayAccess要求
	 *
	 * @param string $key a key identifying the cached value
	 * @return boolean
	 */
	public function offsetExists($key)
	{
		return $this->get ( $key ) !== false;
	}

	/**
	 * 从缓存中获取一个Key的值
	 * 这个方法是由接口ArrayAccess要求
	 *
	 * @param string $key a key identifying the cached value
	 * @return mixed the value stored in cache, false if the value is not in the cache or expired.
	 */
	public function offsetGet($key)
	{
		return $this->get ( $key );
	}

	/**
	 * 存储一个Key的值到缓存中
	 * 这个方法是由接口ArrayAccess要求
	 *
	 * @param string $key the key identifying the value to be cached
	 * @param mixed $value the value to be cached
	 */
	public function offsetSet($key, $value)
	{
		$this->set ( $key, $value );
	}

	/**
	 * 从缓存中删除指定的键的值
	 * 这个方法是由接口ArrayAccess要求
	 *
	 * @param string $key the key of the value to be deleted
	 */
	public function offsetUnset($key)
	{
		$this->delete ( $key );
	}
}