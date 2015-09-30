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
 * Leaps\Http\RequestInterface
 *
 * Interface for Leaps\Http\Request
 */
interface RequestInterface
{
	/**
	 * 解析请求
	 */
	public function resolve();

	/**
	 * 从$_REQUEST获取变量
	 *
	 * @param string name 名称
	 * @param string|array filters 过滤器
	 * @param mixed defaultValue 默认值
	 * @return mixed
	 */
	public function input($name = null, $filters = null, $defaultValue = null);

	/**
	 * 从$_POST获取变量
	 *
	 * @param string name
	 * @param string|array filters
	 * @param mixed defaultValue
	 * @return mixed
	 */
	public function getPost($name = null, $filters = null, $defaultValue = null);

	/**
	 * 从$_GET获取变量
	 *
	 * @param string name
	 * @param string|array filters
	 * @param mixed defaultValue
	 * @return mixed
	 */
	public function getQuery($name = null, $filters = null, $defaultValue = null);

	/**
	 * 返回指定的Http Header
	 *
	 * @param string header
	 * @return string
	 */
	public function getHeader($header);

	/**
	 * 从全局_SETVER获取值
	 *
	 * @param string name
	 * @return mixed
	 */
	public function getServer($name, $defaultValue = null);

	/**
	 * 获取HTTP原始请求体
	 *
	 * @return string
	 */
	public function getRawBody();

	/**
	 * 获取 HTTP schema (http/https)
	 *
	 * @return string
	 */
	public function getScheme();

	/**
	 * 获取服务器IP地址
	 *
	 * @return string
	 */
	public function getServerAddr();

	/**
	 * 获取服务器名称
	 *
	 * @return string
	 */
	public function getServerName();

	/**
	 * 获取请求所使用的协议，主机头和端口信息
	 *
	 * @return string
	 */
	public function getHttpHost();

	/**
	 * 获取用户的IP地址
	 *
	 * @param boolean trustForwardedHeader
	 * @return string
	 */
	public function getUserIp($trustForwardedHeader = false);

	/**
	 * 获取请求的方法
	 *
	 * @return string
	 */
	public function getMethod();

	/**
	 * 获取用户代理字符串
	 *
	 * @return string
	 */
	public function getUserAgent();

	/**
	 * 获取请求来源
	 *
	 * @return string
	 */
	public function getHttpReferrer();

	/**
	 * 获取上传的文件 Leaps\Http\Request\FileInterface
	 *
	 * @param boolean notErrored
	 * @return Leaps\Http\Request\FileInterface[]
	 */
	public function getUploadedFiles($notErrored = false);

	/**
	 * 获取客户端要求的 mime/types
	 *
	 * @return array
	 */
	public function getAcceptableContent();

	/**
	 * Gets best mime/type accepted by the browser/client from $_SERVER['HTTP_ACCEPT']
	 *
	 * @return array
	 */
	public function getBestAccept();

	/**
	 * Gets charsets array and their quality accepted by the browser/client from $_SERVER['HTTP_ACCEPT_CHARSET']
	 *
	 * @return array
	 */
	public function getClientCharsets();

	/**
	 * Gets best charset accepted by the browser/client from $_SERVER['HTTP_ACCEPT_CHARSET']
	 *
	 * @return string
	 */
	public function getBestCharset();

	/**
	 * Gets languages array and their quality accepted by the browser/client from _SERVER['HTTP_ACCEPT_LANGUAGE']
	 *
	 * @return array
	 */
	public function getLanguages();

	/**
	 * Gets best language accepted by the browser/client from $_SERVER['HTTP_ACCEPT_LANGUAGE']
	 *
	 * @return string
	 */
	public function getBestLanguage();

	/**
	 * Gets auth info accepted by the browser/client from $_SERVER['PHP_AUTH_USER']
	 *
	 * @return array
	 */
	public function getBasicAuth();

	/**
	 * Gets auth info accepted by the browser/client from $_SERVER['PHP_AUTH_DIGEST']
	 *
	 * @return array
	 */
	public function getDigestAuth();

	/**
	 * 检查 $_REQUEST 是否含有指定的键
	 *
	 * @param string name
	 * @return boolean
	 */
	public function has($name);

	/**
	 * 检查 $_POST 是否含有指定的键
	 *
	 * @param string name
	 * @return boolean
	 */
	public function hasPost($name);

	/**
	 * 检查 $_GET 是否含有指定的键
	 *
	 * @param string name
	 * @return boolean
	 */
	public function hasQuery($name);

	/**
	 * 检查Server是否含有指定的键
	 *
	 * @param string name
	 * @return mixed
	 */
	public function hasServer($name);

	/**
	 * 检查文件是否上传
	 *
	 * @param boolean notErrored
	 * @return boolean
	 */
	public function hasFiles($notErrored = false);

	/**
	 * 是否是AJAX请求
	 *
	 * @return boolean
	 */
	public function isAjax();

	/**
	 * 是否是SOAP请求
	 *
	 * @return boolean
	 */
	public function isSoapRequested();

	/**
	 * 是否有任何要求使用安全层
	 *
	 * @return boolean
	 */
	public function isSecureRequest();

	/**
	 * 检测请求
	 *
	 * @param string|array methods
	 * @return boolean
	 */
	public function isMethod($methods);

	/**
	 * 是否是POST请求
	 *
	 * @return boolean
	 */
	public function isPost();

	/**
	 *
	 * 是否是GET请求
	 *
	 * @return boolean
	 */
	public function isGet();

	/**
	 * 是否是PUT请求
	 *
	 * @return boolean
	 */
	public function isPut();

	/**
	 * 是否是HEAD请求
	 *
	 * @return boolean
	 */
	public function isHead();

	/**
	 * 是否是Delete请求
	 *
	 * @return boolean
	 */
	public function isDelete();

	/**
	 * 是否是OPTIONS请求
	 *
	 * @return boolean
	 */
	public function isOptions();
}