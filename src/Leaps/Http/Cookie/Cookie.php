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
namespace Leaps\Http\Cookie;

use Leaps\Di\Injectable;
use Leaps\Http\Response\Exception;

/**
 * Leaps\Http\Cookie
 *
 * Provide OO wrappers to manage a HTTP cookie
 */
class Cookie extends Injectable
{
	/**
	 * Cookie名称
	 *
	 * @var string
	 */
	protected $_name;

	/**
	 * Cookie值
	 *
	 * @var string
	 */
	protected $_value;

	/**
	 * Cookie有效期
	 *
	 * @var int
	 */
	protected $_expire;

	/**
	 * Cookie作用路径
	 *
	 * @var string
	 */
	protected $_path = "/";

	/**
	 * Cookie作用域
	 *
	 * @var string
	 */
	protected $_domain;

	/**
	 * 规定是否通过安全的 HTTPS 连接来传输 cookie。
	 *
	 * @var boolean
	 */
	protected $_secure;

	/**
	 * 是否允许JS读取Cookie
	 *
	 * @var boolean
	 */
	protected $_httpOnly = true;

	/**
	 * 过滤器实例
	 *
	 * @var unknown
	 */
	protected $filter;
	protected $_readed = false;
	protected $_restored = false;
	protected $_useEncryption = false;

	/**
	 * 构造方法
	 *
	 * @param string Cookie名称
	 * @param mixed Cookie值
	 * @param int Cookie有效期
	 * @param string Cookie作用路径
	 * @param boolean 是否通过安全的 HTTPS 连接来传输 cookie
	 * @param string Cookie作用域
	 * @param boolean 是否允许JS读取Cookie
	 */
	public function __construct($name, $value = null, $expire = 0, $path = "/", $secure = null, $domain = null, $httpOnly = null)
	{
		$this->_name = $name;
		if (! is_null ( $value )) {
			$this->_value = $value;
		}
		$this->_expire = $expire;
		if (! is_null ( $path )) {
			$this->_path = $path;
		}
		if (is_null ( $secure )) {
			$this->_secure = $secure;
		}
		if (! is_null ( $domain )) {
			$this->_domain = $domain;
		}
		if (! is_null ( $httpOnly )) {
			$this->_httpOnly = $httpOnly;
		}
	}

	/**
	 * 设置Cookie值
	 *
	 * @param string value
	 * @return Leaps\Http\Cookie
	 */
	public function setValue($value)
	{
		$this->_value = $value;
		$this->_readed = true;
		return $this;
	}

	/**
	 * 获取Cookie值
	 *
	 * @param string|array filters
	 * @param string defaultValue
	 * @return mixed
	 */
	public function getValue($filters = null, $defaultValue = null)
	{
		if (! $this->_restored) {
			$this->restore ();
		}
		if ($this->_readed === false) {
			if (isset ( $_COOKIE [$this->_name] )) {
				if ($this->_useEncryption) {
					$crypt = $this->_dependencyInjector->getShared ( "crypt" );
					/**
					 * Decrypt the value also decoding it with base64
					*/
					$decryptedValue = $crypt->decryptBase64 ( $_COOKIE [$this->_name] );
				} else {
					$decryptedValue = $_COOKIE [$this->_name];
				}
				/**
				 * Update the decrypted value
				 */
				$this->_value = $decryptedValue;
				if ($filters !== null) {
					$filter = $this->filter;
					if (! is_object ( $this->filter )) {
						$filter = $this->_dependencyInjector->getShared ( "filter" );
						$this->filter = $filter;
					}

					return $filter->sanitize ( $decryptedValue, $filters );
				}

				/**
				 * Return the value without filtering
				 */
				return $decryptedValue;
			}
			return $defaultValue;
		}

		return $this->_value;
	}

	/**
	 * 发送Cookie到Http Client
	 *
	 * @return Leaps\Http\Cookie
	 */
	public function send()
	{
		if (! is_object ( $this->_dependencyInjector )) {
			throw new Exception ( "A dependency injection object is required to access the 'session' service" );
		}
		$definition = [ ];
		if ($this->_expire != 0) {
			$definition ["expire"] = $this->_expire;
		}
		if (! empty ( $this->_path )) {
			$definition ["path"] = $this->_path;
		}
		if (! empty ( $this->_domain )) {
			$definition ["domain"] = $this->_domain;
		}
		if (! empty ( $this->_secure )) {
			$definition ["secure"] = $this->_secure;
		}
		if (! empty ( $this->_httpOnly )) {
			$definition ["httpOnly"] = $this->_httpOnly;
		}
		/**
		 * The definition is stored in session
		 */
		if (count ( $definition )) {
			$session = $this->_dependencyInjector->getShared ( "session" );
			$session->set ( "_LEAPSCOOKIE_" . $this->_name, $definition );
		}
		if ($this->_useEncryption) {
			if (! empty ( $this->_value )) {
				if (! is_object ( $this->_dependencyInjector )) {
					throw new Exception ( "A dependency injection object is required to access the 'crypt' service" );
				}
				$crypt = $this->_dependencyInjector->getShared ( "crypt" );
				$encryptValue = $crypt->encryptBase64 ( $this->_value );
			} else {
				$encryptValue = $this->_value;
			}
		} else {
			$encryptValue = $this->_value;
		}
		/**
		 * Sets the cookie using the standard 'setcookie' function
		 */
		setcookie ( $this->_name, $encryptValue, $this->_expire, $this->_path, $this->_domain, $this->_secure, $this->_httpOnly );
		return $this;
	}

	/**
	 * Reads the cookie-related info from the SESSION to restore the cookie as it was set
	 * This method is automatically called internally so normally you don't need to call it
	 *
	 * @return Leaps\Http\Cookie
	 */
	public function restore()
	{
		if (! $this->_restored) {
			if (is_object ( $this->_dependencyInjector )) {
				$session = $this->_dependencyInjector->getShared ( "session" );
				$definition = $session->get ( "_LEAPSCOOKIE_" . $this->_name );
				if (is_array ( $definition )) {
					if (isset ( $definition ["expire"] )) {
						$this->_expire = $definition ["expire"];
					}
					if (isset ( $definition ["domain"] )) {
						$this->_domain = $definition ["domain"];
					}

					if (isset ( $definition ["path"] )) {
						$this->_path = $definition ["path"];
					}

					if (isset ( $definition ["secure"] )) {
						$this->_secure = $definition ["secure"];
					}

					if (isset ( $definition ["httpOnly"] )) {
						$this->_httpOnly = $definition ["httpOnly"];
					}
				}
			}
			$this->_restored = true;
		}
		return $this;
	}

	/**
	 * 删除Cookie
	 */
	public function delete()
	{
		if (is_object ( $this->_dependencyInjector )) {
			$session = $this->_dependencyInjector->getShared ( "session" );
			$session->remove ( "_LEAPSCOOKIE_" . $this->_name );
		}
		$this->_value = null;
		setcookie ( $this->_name, null, time () - 691200, $this->_path, $this->_domain, $this->_secure, $this->_httpOnly );
	}

	/**
	 * 设置Cookie自动加密解密
	 *
	 * @param boolean useEncryption
	 * @return Leaps\Http\Cookie
	 */
	public function useEncryption($useEncryption)
	{
		$this->_useEncryption = $useEncryption;
		return $this;
	}

	/**
	 * 是否启用了自动加密/解密
	 *
	 * @return boolean
	 */
	public function isUsingEncryption()
	{
		return $this->_useEncryption;
	}

	/**
	 * 设置Cookie有效期
	 *
	 * @param int expire
	 * @return Leaps\Http\Cookie
	 */
	public function setExpiration($expire)
	{
		if (! $this->_restored) {
			$this->restore ();
		}
		$this->_expire = $expire;
		return $this;
	}

	/**
	 * 返回Cookie有效期
	 *
	 * @return string
	 */
	public function getExpiration()
	{
		if (! $this->_restored) {
			$this->restore ();
		}
		return $this->_expire;
	}

	/**
	 * 设置Cookie路径
	 *
	 * @param string path
	 * @return Leaps\Http\Cookie
	 */
	public function setPath($path)
	{
		if (! $this->_restored) {
			$this->restore ();
		}
		$this->_path = $path;
		return $this;
	}

	/**
	 * 返回Cookie名称
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->_name;
	}

	/**
	 * 返回Cookie路径
	 *
	 * @return string
	 */
	public function getPath()
	{
		if (! $this->_restored) {
			$this->restore ();
		}
		return $this->_path;
	}

	/**
	 * 设置Cookie作用域
	 *
	 * @param string domain
	 * @return Leaps\Http\Cookie
	 */
	public function setDomain($domain)
	{
		if (! $this->_restored) {
			$this->restore ();
		}
		$this->_domain = $domain;
		return $this;
	}

	/**
	 * 返回Cookie作用域
	 *
	 * @return string
	 */
	public function getDomain()
	{
		if (! $this->_restored) {
			$this->restore ();
		}
		return $this->_domain;
	}

	/**
	 * Sets if the cookie must only be sent when the connection is secure (HTTPS)
	 *
	 * @param boolean secure
	 * @return Leaps\Http\Cookie
	 */
	public function setSecure($secure)
	{
		if (! $this->_restored) {
			$this->restore ();
		}
		$this->_secure = $secure;
		return $this;
	}

	/**
	 * Returns whether the cookie must only be sent when the connection is secure (HTTPS)
	 *
	 * @return boolean
	 */
	public function getSecure()
	{
		if (! $this->_restored) {
			$this->restore ();
		}
		return $this->_secure;
	}

	/**
	 * Sets if the cookie is accessible only through the HTTP protocol
	 *
	 * @param boolean httpOnly
	 * @return Leaps\Http\Cookie
	 */
	public function setHttpOnly($httpOnly)
	{
		if (! $this->_restored) {
			$this->restore ();
		}
		$this->_httpOnly = $httpOnly;
		return $this;
	}

	/**
	 * Returns if the cookie is accessible only through the HTTP protocol
	 *
	 * @return boolean
	 */
	public function getHttpOnly()
	{
		if (! $this->_restored) {
			$this->restore ();
		}
		return $this->_httpOnly;
	}

	/**
	 * Magic __toString method converts the cookie's value to string
	 *
	 * @return string
	 */
	public function __toString()
	{
		return ( string ) $this->getValue ();
	}
}