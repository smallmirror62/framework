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

use Closure;
use Iterator;
use Countable;
use ArrayAccess;
use JsonSerializable;

final class Registry implements ArrayAccess, JsonSerializable, Iterator, Countable
{

	/**
	 * 对象所有的属性
	 *
	 * @var array
	 */
	protected $attributes = [ ];

	/**
	 * 创建一个新的容器实例
	 *
	 * @param array|object $attributes 属性
	 * @return void
	 */
	public function __construct($attributes = [])
	{
		foreach ( $attributes as $key => $value ) {
			$this->attributes [$key] = $value;
		}
	}

	/**
	 * 从容器获取属性
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function get($key, $default = null)
	{
		if (array_key_exists ( $key, $this->attributes )) {
			return $this->attributes [$key];
		}
		return $default instanceof Closure ? $default () : $default;
	}

	/**
	 * 获取容器所有属性
	 *
	 * @return array
	 */
	public function getAttributes()
	{
		return $this->attributes;
	}

	/**
	 * 转换容器属性到数组
	 *
	 * @return array
	 */
	public function toArray()
	{
		return $this->attributes;
	}

	/**
	 * 转换对象成JSON
	 *
	 * @return array
	 */
	public function jsonSerialize()
	{
		return $this->toArray ();
	}

	/**
	 * 转换容器实例到JSON
	 *
	 * @param int $options
	 * @return string
	 */
	public function toJson($options = 0)
	{
		return json_encode ( $this->toArray (), $options );
	}

	/**
	 * 判断属性是否存在
	 *
	 * @param string $offset
	 * @return bool
	 */
	public function offsetExists($offset)
	{
		return isset ( $this->{$offset} );
	}

	/**
	 * 获取属性
	 *
	 * @param string $offset
	 * @return mixed
	 */
	public function offsetGet($offset)
	{
		return $this->{$offset};
	}

	/**
	 * 设置属性
	 *
	 * @param string $offset
	 * @param mixed $value
	 * @return void
	 */
	public function offsetSet($offset, $value)
	{
		$this->{$offset} = $value;
	}

	/**
	 * 删除属性
	 *
	 * @param string $offset
	 * @return void
	 */
	public function offsetUnset($offset)
	{
		unset ( $this->{$offset} );
	}

	/**
	 * Handle dynamic calls to the container to set attributes.
	 *
	 * @param string $method
	 * @param array $parameters
	 * @return $this
	 */
	public function __call($method, $parameters)
	{
		$this->attributes [$method] = count ( $parameters ) > 0 ? $parameters [0] : true;
		return $this;
	}

	/**
	 * 获取属性
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __get($key)
	{
		return $this->get ( $key );
	}

	/**
	 * 设置属性
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function __set($key, $value)
	{
		$this->attributes [$key] = $value;
	}

	/**
	 * 判断属性是否设置
	 *
	 * @param string $key
	 * @return void
	 */
	public function __isset($key)
	{
		return isset ( $this->attributes [$key] );
	}

	/**
	 * 删除属性
	 *
	 * @param string $key
	 * @return void
	 */
	public function __unset($key)
	{
		unset ( $this->attributes [$key] );
	}

	/**
	 * 获取属性数量
	 *
	 * @return int
	 */
	public final function count()
	{
		return count ( $this->attributes );
	}

	/**
	 * 将光标移到注册表中的下一行
	 */
	public final function next()
	{
		next ( $this->attributes );
	}

	/**
	 * 获取注册表中的活动行的指针数目
	 *
	 * @return int
	 */
	public final function key()
	{
		return key ( $this->attributes );
	}

	/**
	 * 移动指针到注册表开头
	 */
	public final function rewind()
	{
		reset ( $this->attributes );
	}

	/**
	 * 检查迭代器是否有效
	 */
	public function valid()
	{
		return key ( $this->attributes ) !== null;
	}

	/**
	 */
	public function current()
	{
		return current ( $this->attributes );
	}
}