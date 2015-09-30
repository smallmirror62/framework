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

use Leaps\Di\Injectable;
use Leaps\Http\Cookie\Cookie;

class Cookies extends Injectable implements CookiesInterface
{

	/**
	 * 将Cookie注射到响应
	 *
	 * @var boolean
	 */
	protected $_registered = false;

	/**
	 * 是否启用Cookie的自动加密/解密
	 *
	 * @var boolean
	 */
	protected $_useEncryption = true;

	/**
	 * Cookie集合
	 *
	 * @var array
	 */
	protected $_cookies;

	/**
	 * (non-PHPdoc)
	 *
	 * @see \Leaps\Http\CookiesInterface::enableEncryption()
	 */
	public function useEncryption($useEncryption)
	{
		$this->_useEncryption = $useEncryption;
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \Leaps\Http\CookiesInterface::isEnableEncryption()
	 */
	public function isUsingEncryption()
	{
		return $this->_useEncryption;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \Leaps\Http\CookiesInterface::set()
	 */
	public function set($name, $value = null, $expire = 0, $path = "/", $secure = null, $domain = null, $httpOnly = null)
	{
		if (! isset ( $this->_cookies [$name] )) {
			$cookie = new Cookie ( $name, $value, $expire, $path, $secure, $domain, $httpOnly );
			$cookie->setDi ( $this->getDI () );
			$cookie->useEncryption ( $this->_useEncryption );
			$this->_cookies [$name] = $cookie;
		} else {
			$this->_cookies [$name]->setValue ( $value );
			$this->_cookies [$name]->setExpiration ( $expire );
			$this->_cookies [$name]->setPath ( $path );
			$this->_cookies [$name]->setSecure ( $secure );
			$this->_cookies [$name]->setDomain ( $domain );
			$this->_cookies [$name]->setHttpOnly ( $httpOnly );
		}

		/**
		 * 注册Cookie到响应
		 */
		if ($this->_registered === false) {
			$response = $this->_dependencyInjector->getShared ( "response" );
			$response->setCookies ( $this );
		}
		return $this;
	}

	/**
	 * 获取Cookie值
	 *
	 * @param string name
	 * @return Leaps\Http\Cookie
	 */
	public function get($name)
	{
		if (isset ( $this->_cookies [$name] )) {
			return $this->_cookies [$name];
		}
		$cookie = new Cookie ( $name );
		if (is_object ( $this->_dependencyInjector )) {
			$cookie->setDi ( $this->_dependencyInjector );
			$cookie->useEncryption ( $this->_useEncryption );
		}
		$this->_cookies [$name] = $cookie;
		return $cookie;
	}

	/**
	 * 检查Cookie是否存在
	 *
	 * @param string name
	 * @return boolean
	 */
	public function has($name)
	{
		if (isset ( $this->_cookies [$name] )) {
			return true;
		}
		if (isset ( $_COOKIE [$name] )) {
			return true;
		}
		return false;
	}

	/**
	 * 删除Cookie
	 *
	 * @param string name
	 * @return boolean
	 */
	public function delete($name)
	{
		if (isset ( $this->_cookies [$name] )) {
			$this->_cookies [$name]->delete ();
			return true;
		}
		return false;
	}

	/**
	 * 发送Cookie到客户端
	 *
	 * @return boolean
	 */
	public function send()
	{
		if (! headers_sent ()) {
			foreach ( $this->_cookies as $cookie ) {
				$cookie->send ();
			}
			return true;
		}
		return false;
	}

	/**
	 * 重置Cookie集合
	 *
	 * @return Leaps\Http\Cookies
	 */
	public function reset()
	{
		$this->_cookies = [ ];
		return $this;
	}
}