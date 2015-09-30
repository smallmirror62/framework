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
 * Leaps\Http\Response\CookiesInterface
 *
 * Interface for Leaps\Http\Response\Cookies
 */
interface CookiesInterface
{

	/**
	 * 设置Cookie自动加密/解密
	 *
	 * @param boolean useEncryption
	 * @return Leaps\Http\CookiesInterface
	 */
	public function useEncryption($useEncryption);

	/**
	 * 返回是否开启Cookie自动加密/解密
	 *
	 * @return boolean
	*/
	public function isUsingEncryption();

	/**
	 * 设置一个Cookie
	 *
	 * @param string name 名称
	 * @param mixed value 值
	 * @param int expire 有效期
	 * @param string path 有效路径
	 * @param boolean secure
	 * @param string domain
	 * @param boolean httpOnly
	 * @return Leaps\Http\CookiesInterface
	*/
	public function set($name, $value = null, $expire = 0, $path = '/', $secure = null, $domain = null, $httpOnly = null);

	/**
	 * 获取Cookie
	 *
	 * @param string name
	 * @return Leaps\Http\Cookie\Cookie
	*/
	public function get($name);

	/**
	 * 检查Cookie是否存在
	 *
	 * @param string name
	 * @return boolean
	*/
	public function has($name);

	/**
	 * 删除Cookie
	 *
	 * @param string name
	 * @return boolean
	*/
	public function delete($name);

	/**
	 * 发送Cookie到客户端
	 *
	 * @return boolean
	*/
	public function send();

	/**
	 * 重置Cookie
	 *
	 * @return Phalcon\Http\CookiesInterface
	*/
	public function reset();
}
