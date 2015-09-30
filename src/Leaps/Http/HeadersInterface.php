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
namespace Leaps\Http;

/**
 * Leaps\Http\HeadersInterface
 *
 * Interface for Leaps\Http\Headers compatible bags
 */
interface HeadersInterface
{

	/**
	 * Sets a header to be sent at the end of the request
	 *
	 * @param string name
	 * @param string value
	 */
	public function set($name, $value);

	/**
	 * Gets a header value from the internal bag
	 *
	 * @param string name
	 * @return string
	*/
	public function get($name, $default = null, $first = true);

	/**
	 * 在请求结束时设置一个原始头
	 *
	 * @param string header
	*/
	public function setRaw($header);

	/**
	 * 发送头到客户端
	 *
	 * @return boolean
	*/
	public function send();

	/**
	 * 重置Header
	 *
	*/
	public function reset();

	/**
	 * 恢复 Leaps\Http\Headers 对象
	 *
	 * @param array data
	 * @return Leaps\Http\HeadersInterface
	*/
	public static function __set_state($data);

}