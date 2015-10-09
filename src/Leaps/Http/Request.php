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
use Leaps\Http\Request\File;
use Leaps\Core\InvalidConfigException;

class Request extends Injectable implements RequestInterface
{

	/**
	 * 路由组件实例和名称
	 *
	 * @var \Leaps\Router\UrlManager
	 */
	protected $_router = 'router';

	/**
	 * 参数过滤器实例
	 *
	 * @var \Leaps\Filter\Filter
	 */
	protected $_filter = 'filter';

	/**
	 * 请求的原始内容
	 *
	 * @var string
	 */
	protected $_rawBody;

	protected $_putCache;

	/**
	 * 请求头
	 *
	 * @var \Leaps\Http\Headers
	 */
	protected $_headers;
	protected $_url;
	protected $_hostInfo;

	/**
	 * 请求的脚本路径
	 *
	 * @var string
	 */
	protected $_scriptUrl;

	/**
	 * 网站跟路径
	 *
	 * @var string
	 */
	protected $_baseUrl;
	protected $_pathInfo;

	/**
	 * (non-PHPdoc)
	 *
	 * @see \Leaps\Http\RequestInterface::resolve()
	 */
	public function resolve()
	{
		if (! is_object ( $this->_router )) {
			$this->_router = $this->_dependencyInjector->getShared ( $this->_router );
		}
		$result = $this->_router->parseRequest ( $this );
		if ($result !== false) {
			list ( $route, $params ) = $result;
			$_GET = array_merge ( $_GET, $params );
			return [ $route,$_GET ];
		} else {
			throw new \Exception ( "Page not found." );
		}
	}

	/**
	 * 从 $_REQUEST 获取一个变量，如果输入为空，将返回 $_REQUEST 数组
	 *
	 * <code>
	 * //Returns value from $_REQUEST["user_email"] without sanitizing
	 * $userEmail = $request->get("user_email");
	 *
	 * //Returns value from $_REQUEST["user_email"] with sanitizing
	 * $userEmail = $request->get("user_email", "email");
	 * </code>
	 */
	public function input($name = null, $filters = null, $defaultValue = null, $notAllowEmpty = false, $noRecursive = false)
	{
		return $this->getHelper ( $_REQUEST, $name, $filters, $defaultValue, $notAllowEmpty, $noRecursive );
	}

	/**
	 * 从 $_POST 获取一个变量，如果输入为空，将返回 $_POST 数组
	 *
	 * <code>
	 * //Returns value from $_POST["user_email"] without sanitizing
	 * $userEmail = $request->getPost("user_email");
	 *
	 * //Returns value from $_POST["user_email"] with sanitizing
	 * $userEmail = $request->getPost("user_email", "email");
	 * </code>
	 */
	public function getPost($name = null, $filters = null, $defaultValue = null, $notAllowEmpty = false, $noRecursive = false)
	{
		return $this->getHelper ( $_POST, $name, $filters, $defaultValue, $notAllowEmpty, $noRecursive );
	}

	/**
	 * 从PUT请求中获取一个变量
	 *
	 * <code>
	 * //Returns value from $_PUT["user_email"] without sanitizing
	 * $userEmail = $request->getPut("user_email");
	 *
	 * //Returns value from $_PUT["user_email"] with sanitizing
	 * $userEmail = $request->getPut("user_email", "email");
	 * </code>
	 */
	public function getPut($name = null, $filters = null, $defaultValue = null, $notAllowEmpty = false, $noRecursive = false)
	{
		$put = $this->_putCache;
		if (! is_array ( $put )) {
			$put = [ ];
			parse_str ( $this->getRawBody(), $put );
			$this->_putCache = $put;
		}
		return $this->getHelper ( $put, $name, $filters, $defaultValue, $notAllowEmpty, $noRecursive );
	}

	/**
	 * Gets variable from $_GET superglobal applying filters if needed
	 * If no parameters are given the $_GET superglobal is returned
	 *
	 * <code>
	 * //Returns value from $_GET["id"] without sanitizing
	 * $id = $request->getQuery("id");
	 *
	 * //Returns value from $_GET["id"] with sanitizing
	 * $id = $request->getQuery("id", "int");
	 *
	 * //Returns value from $_GET["id"] with a default value
	 * $id = $request->getQuery("id", null, 150);
	 * </code>
	 */
	public function getQuery($name = null, $filters = null, $defaultValue = null, $notAllowEmpty = false, $noRecursive = false)
	{
		return $this->getHelper ( $_GET, $name, $filters, $defaultValue, $notAllowEmpty, $noRecursive );
	}

	/**
	 * Helper to get data from superglobals, applying filters if needed.
	 * If no parameters are given the superglobal is returned.
	 */
	protected final function getHelper($source, $name = null, $filters = null, $defaultValue = null, $notAllowEmpty = false, $noRecursive = false)
	{
		if (is_null ( $name )) {
			return $source;
		}
		if (! isset ( $source [$name] )) {
			return $defaultValue;
		}
		$value = '';
		if (! is_null ( $filters )) {
			if (! is_object ( $this->_filter )) {
				$this->_filter = $this->_dependencyInjector->getShared ( $this->_filter );
			}
			$value = $this->_filter->sanitize ( $value, $filters, $noRecursive );
		}
		if (empty ( $value ) && $notAllowEmpty === true) {
			return $defaultValue;
		}
		return $value;
	}

	/**
	 * 返回Server的值
	 *
	 * @param string name
	 * @return mixed
	 */
	public function getServer($name, $defaultValue = null)
	{
		if (isset ( $_SERVER [$name] )) {
			return $_SERVER [$name];
		}
		return $defaultValue;
	}

	/**
	 * 从请求数据获取HTTP头
	 *
	 * @param string header
	 * @return string
	 */
	public final function getHeader($header)
	{
		if (isset ( $_SERVER [$header] )) {
			return $_SERVER [$header];
		} elseif (isset ( $_SERVER ["HTTP_" . $header] )) {
			return $_SERVER ["HTTP_" . $header];
		}
		return "";
	}

	/**
	 * 获取HTTP模式 (http/https)
	 *
	 * @return string
	 */
	public function getScheme()
	{
		$https = $this->getServer ( "HTTPS" );
		if ($https) {
			$scheme = $https == "off" ? "http" : 'https';
		} else {
			$scheme = "http";
		}
		return $scheme;
	}

	/**
	 * 获取POST原始请求体
	 *
	 * @return string
	 */
	public function getRawBody()
	{
		if (empty ( $this->_rawBody )) {
			$this->_rawBody = file_get_contents ( "php://input" );
		}
		return $this->_rawBody;
	}

	/**
	 * 获取POST原始请求体并解析JSON
	 *
	 * @param boolean associative
	 * @return string
	 */
	public function getJsonRawBody($associative = false)
	{
		$rawBody = $this->getRawBody ();
		if (is_string ( $rawBody )) {
			return json_decode ( $rawBody, $associative );
		}
		return false;
	}

	/**
	 * 获取服务器IP地址
	 *
	 * @return string
	 */
	public function getServerAddr()
	{
		if (isset ( $_SERVER ["SERVER_ADDR"] )) {
			return $_SERVER ["SERVER_ADDR"];
		}
		return gethostbyname ( "localhost" );
	}

	/**
	 * 获取活动服务器名称
	 *
	 * @return string
	 */
	public function getServerName()
	{
		if (isset ( $_SERVER ["SERVER_NAME"] )) {
			return $_SERVER ["SERVER_NAME"];
		}
		return "localhost";
	}

	/**
	 * 获取服务器主机名和端口
	 *
	 * @return string
	 */
	public function getHttpHost()
	{
		$httpHost = $this->getServer ( "HTTP_HOST" );
		if ($httpHost) {
			return $httpHost;
		}
		$scheme = $this->getScheme ();
		$name = $this->getServer ( "SERVER_NAME" );
		$port = $this->getServer ( "SERVER_PORT" );
		if ($scheme == "http" && $port == 80) {
			return $name;
		}
		if ($scheme == "https" && $port == "443") {
			return $name;
		}
		return $name . ":" . $port;
	}

	/**
	 * 返回当前请求的相对URI
	 *
	 * @return string the currently requested relative URL. Note that the URI returned is URL-encoded.
	 * @throws InvalidConfigException if the URL cannot be determined due to unusual server configuration
	 */
	public function getUrl()
	{
		if ($this->_url === null) {
			$this->_url = $this->resolveRequestUri ();
		}
		return $this->_url;
	}

	/**
	 * 获取请求的客户端的IPV4地址
	 * This method search in _SERVER['REMOTE_ADDR'] and optionally in _SERVER['HTTP_X_FORWARDED_FOR']
	 */
	public function getUserIp($trustForwardedHeader = false)
	{
		$address = null;
		if (isset ( $_SERVER ['HTTP_CLIENT_IP'] ) && $_SERVER ['HTTP_CLIENT_IP'] != null) {
			$address = $_SERVER ['HTTP_CLIENT_IP'];
		} elseif (isset ( $_SERVER [' HTTP_X_FORWARDED_FOR'] ) && $_SERVER [' HTTP_X_FORWARDED_FOR'] != null) {
			$address = strtok ( $_SERVER [' HTTP_X_FORWARDED_FOR'], ',' );
		} elseif (isset ( $_SERVER ['HTTP_PROXY_USER'] ) && $_SERVER ['HTTP_PROXY_USER'] != null) {
			$address = $_SERVER ['HTTP_PROXY_USER'];
		} elseif (isset ( $_SERVER ['REMOTE_ADDR'] ) && $_SERVER ['REMOTE_ADDR'] != null) {
			$address = $_SERVER ['REMOTE_ADDR'];
		} else {
			$address = "0.0.0.0";
		}
		return $address;
	}

	/**
	 * 返回当前请求的方法 (比如 GET, POST, HEAD, PUT, PATCH, DELETE)。
	 *
	 * @return string 请求方法,比如 GET, POST, HEAD, PUT, PATCH, DELETE。
	 */
	public function getMethod()
	{
		if (isset ( $_POST [$this->methodParam] )) {
			return strtoupper ( $_POST [$this->methodParam] );
		} elseif (isset ( $_SERVER ["HTTP_X_HTTP_METHOD_OVERRIDE"] )) {
			return strtoupper ( $_SERVER ["HTTP_X_HTTP_METHOD_OVERRIDE"] );
		} elseif (isset ( $_SERVER ["REQUEST_METHOD"] )) {
			return strtoupper ( $_SERVER ["REQUEST_METHOD"] );
		} else {
			return "GET";
		}
	}

	/**
	 * 返回 UserAgent
	 *
	 * @return \Leaps\Http\UserAgent
	 */
	public function getUserAgent()
	{
		return new UserAgent();
	}

	/**
	 * 从 Leaps\Http\Request\File 实例获取附加文件
	 */
	public function getUploadedFiles($onlySuccessful = false)
	{
		$files = [ ];
		if (count ( $_FILES ) > 0) {
			foreach ( $_FILES as $prefix => $input ) {
				if (is_array ( $input ["name"] )) {
					$smoothInput = $this->smoothFiles ( $input ["name"], $input ["type"], $input ["tmp_name"], $input ["size"], $input ["error"], $prefix );
					foreach ( $smoothInput as $file ) {
						if ($onlySuccessful == false || $file ["error"] == UPLOAD_ERR_OK) {
							$dataFile = [ "name" => $file ["name"],"type" => $file ["type"],"tmp_name" => $file ["tmp_name"],"size" => $file ["size"],"error" => $file ["error"] ];
							$files [] = new File ( $dataFile, $file ["key"] );
						}
					}
				} else {
					if ($onlySuccessful == false || $input ["error"] == UPLOAD_ERR_OK) {
						$files [] = new File ( $input, $prefix );
					}
				}
			}
		}
		return $files;
	}

	/**
	 * 返回输入脚本的物理路径
	 *
	 * @return string the entry script file path
	 */
	public function getScriptFile()
	{
		if (isset ( $_SERVER ["SCRIPT_FILENAME"] )) {
			return $_SERVER ["SCRIPT_FILENAME"];
		}
		return "";
	}

	/**
	 * 返回当前请求的模式和主机部分URL。
	 * The returned URL does not have an ending slash.
	 * By default this is determined based on the user request information.
	 * You may explicitly specify it by setting the [[setHostInfo()|hostInfo]] property.
	 *
	 * @return string schema and hostname part (with port number if needed) of the request URL (e.g. `http://www.yiiframework.com`)
	 * @see setHostInfo()
	 */
	public function getHostInfo()
	{
		if (! $this->_hostInfo) {
			$this->_hostInfo = $this->getScheme () . '://' . $this->getHttpHost ();
		}
		return $this->_hostInfo;
	}

	/**
	 * 返回入口脚本的相对URL
	 *
	 * @return string the relative URL of the entry script.
	 * @throws InvalidConfigException if unable to determine the entry script URL
	 */
	public function getScriptUrl()
	{
		if (! $this->_scriptUrl) {
			$scriptFile = $this->getScriptFile ();
			$scriptName = basename ( $scriptFile );
			$pos = strpos ( $_SERVER ["PHP_SELF"], "/" . $scriptName );
			if (basename ( $_SERVER ["SCRIPT_NAME"] ) === $scriptName) {
				$this->_scriptUrl = $_SERVER ["SCRIPT_NAME"];
			} elseif (basename ( $_SERVER ["PHP_SELF"] ) === $scriptName) {
				$this->_scriptUrl = $_SERVER ["PHP_SELF"];
			} elseif (isset ( $_SERVER ["ORIG_SCRIPT_NAME"] ) && basename ( $_SERVER ["ORIG_SCRIPT_NAME"] ) === $scriptName) {
				$this->_scriptUrl = $_SERVER ["ORIG_SCRIPT_NAME"];
			} elseif ($pos !== false) {
				$this->_scriptUrl = substr ( $_SERVER ["SCRIPT_NAME"], 0, $pos ) . "/" . $scriptName;
			} elseif (! empty ( $_SERVER ["DOCUMENT_ROOT"] ) && strpos ( scriptFile, $_SERVER ["DOCUMENT_ROOT"] ) === 0) {
				$this->_scriptUrl = str_replace ( "\\", "/", str_replace ( $_SERVER ["DOCUMENT_ROOT"], "", $scriptFile ) );
			} else {
				throw new InvalidConfigException ( "Unable to determine the entry script URL." );
			}
		}
		return $this->_scriptUrl;
	}

	/**
	 * 返回应用程序的相对URL。
	 *
	 * @return string the relative URL for the application
	 * @see setScriptUrl()
	 */
	public function getBaseUrl()
	{
		if ($this->_baseUrl === null) {
			$this->_baseUrl = rtrim ( dirname ( $this->getScriptUrl () ), "\\/" );
		}
		return $this->_baseUrl;
	}

	/**
	 * 返回请求内容类型
	 *
	 * @return mixed
	 */
	public function getContentType()
	{
		if (isset ( $_SERVER ["CONTENT_TYPE"] )) {
			return $_SERVER ["CONTENT_TYPE"];
		} elseif ($_SERVER ["HTTP_CONTENT_TYPE"]) {
			return $_SERVER ["HTTP_CONTENT_TYPE"];
		} else {
			return null;
		}
	}

	/**
	 * Gets an array with mime/types and their quality accepted by the browser/client from _SERVER["HTTP_ACCEPT"]
	 */
	public function getAcceptableContent()
	{
		return $this->_getQualityHeader ( "HTTP_ACCEPT", "accept" );
	}

	/**
	 * Gets best mime/type accepted by the browser/client from _SERVER["HTTP_ACCEPT"]
	 */
	public function getBestAccept()
	{
		return $this->_getBestQuality ( $this->getAcceptableContent (), "accept" );
	}

	/**
	 * Gets a charsets array and their quality accepted by the browser/client from _SERVER["HTTP_ACCEPT_CHARSET"]
	 */
	public function getClientCharsets()
	{
		return $this->_getQualityHeader ( "HTTP_ACCEPT_CHARSET", "charset" );
	}

	/**
	 * Gets best charset accepted by the browser/client from _SERVER["HTTP_ACCEPT_CHARSET"]
	 */
	public function getBestCharset()
	{
		return $this->_getBestQuality ( $this->getClientCharsets (), "charset" );
	}

	/**
	 * Gets languages array and their quality accepted by the browser/client from _SERVER["HTTP_ACCEPT_LANGUAGE"]
	 */
	public function getLanguages()
	{
		return $this->_getQualityHeader ( "HTTP_ACCEPT_LANGUAGE", "language" );
	}

	/**
	 * Gets best language accepted by the browser/client from _SERVER["HTTP_ACCEPT_LANGUAGE"]
	 */
	public function getBestLanguage()
	{
		return $this->_getBestQuality ( $this->getLanguages (), "language" );
	}

	/**
	 * Gets auth info accepted by the browser/client from $_SERVER['PHP_AUTH_USER']
	 *
	 * @return array
	 */
	public function getBasicAuth()
	{
		if (isset ( $_SERVER ["PHP_AUTH_USER"] ) && isset ( $_SERVER ["PHP_AUTH_PW"] )) {
			$auth = [ ];
			$auth ["username"] = $_SERVER ["PHP_AUTH_USER"];
			$auth ["password"] = $_SERVER ["PHP_AUTH_PW"];
			return $auth;
		}
		return null;
	}

	/**
	 * Gets auth info accepted by the browser/client from $_SERVER['PHP_AUTH_DIGEST']
	 *
	 * @return array
	 */
	public function getDigestAuth()
	{
		$auth = [ ];
		if (isset ( $_SERVER ["PHP_AUTH_DIGEST"] )) {
			$matches = [ ];
			if (! preg_match_all ( "#(\\w+)=(['\"]?)([^'\" ,]+)\\2#", $_SERVER ["PHP_AUTH_DIGEST"], $matches, 2 )) {
				return $auth;
			}
			if (is_array ( $matches )) {
				foreach ( $matches as $match ) {
					$auth [$match [1]] = $match [3];
				}
			}
		}
		return $auth;
	}

	/**
	 * 返回当前请求的绝对URL
	 * 这是一个快捷连接 [[hostInfo]] 和 [[url]].
	 *
	 * @return string the currently requested absolute URL.
	 */
	public function getAbsoluteUrl()
	{
		return $this->getHostInfo () . $this->getUrl ();
	}

	/**
	 * 返回当前请求的URL的路径信息。
	 *
	 * @return string part of the request URL that is after the entry script and before the question mark.
	 *         Note, the returned path info is already URL-decoded.
	 * @throws InvalidConfigException if the path info cannot be determined due to unexpected server configuration
	 */
	public function getPathInfo()
	{
		if ($this->_pathInfo === null) {
			$this->_pathInfo = $this->resolvePathInfo ();
		}
		return $this->_pathInfo;
	}

	/**
	 * 返回服务器端口
	 *
	 * @return integer server port number
	 */
	public function getServerPort()
	{
		if (isset ( $_SERVER ["SERVER_PORT"] )) {
			return $_SERVER ["SERVER_PORT"];
		}
		return 80;
	}

	/**
	 * 返回用户主机名
	 *
	 * @return string user host name, null if cannot be determined
	 */
	public function getUserHost()
	{
		if (isset ( $_SERVER ["REMOTE_HOST"] )) {
			return $_SERVER ["REMOTE_HOST"];
		}
		return "";
	}

	/**
	 * 后返回的请求URL的问号部分。
	 *
	 * @return string part of the request URL that is after the question mark
	 */
	public function getQueryString()
	{
		if (isset ( $_SERVER ["QUERY_STRING"] )) {
			return $_SERVER ["QUERY_STRING"];
		}
		return "";
	}

	/**
	 * 返回 Etags.
	 *
	 * @return array The entity tags
	 */
	public function getETags()
	{
		if (isset ( $_SERVER ["HTTP_IF_NONE_MATCH"] )) {
			return preg_split ( "/[\s,]+/", $_SERVER ["HTTP_IF_NONE_MATCH"], - 1, PREG_SPLIT_NO_EMPTY );
		} else {
			return [ ];
		}
	}

	/**
	 * 返回请求Headers
	 *
	 * @return array
	 */
	public function getHeaders()
	{
		if ($this->_headers === null) {
			$this->_headers = new Headers ();
			foreach ( $_SERVER as $name => $value ) {
				if (strncmp ( $name, 'HTTP_', 5 ) === 0) {
					$name = str_replace ( ' ', '-', ucwords ( strtolower ( str_replace ( '_', ' ', substr ( $name, 5 ) ) ) ) );
					$this->_headers->add ( $name, $value );
				}
			}
		}
		return $this->_headers;
	}

	/**
	 * 返回URL来路
	 *
	 * @return string
	 */
	public function getHttpReferrer()
	{
		if (isset ( $_SERVER ["HTTP_REFERER"] )) {
			return $_SERVER ["HTTP_REFERER"];
		}
		return "";
	}

	/**
	 * 检查 $_REQUEST 是否含有指定的键
	 *
	 * @param string $name
	 */
	public function has($name)
	{
		return isset ( $_REQUEST [$name] );
	}

	/**
	 * Checks whether $_POST superglobal has certain index
	 */
	public function hasPost($name)
	{
		return isset ( $_POST [$name] );
	}

	/**
	 * Checks whether the PUT data has certain index
	 */
	public function hasPut($name)
	{
		$put = $this->getPut ();
		return isset ( $put [$name] );
	}

	/**
	 * Checks whether $_GET superglobal has certain index
	 */
	public function hasQuery($name)
	{
		return isset ( $_GET [$name] );
	}

	/**
	 * Checks whether $_SERVER superglobal has certain index
	 */
	public final function hasServer($name)
	{
		return isset ( $_SERVER [$name] );
	}

	/**
	 * Checks whether request include attached files
	 */
	public function hasFiles($onlySuccessful = false)
	{
		$numberFiles = 0;
		if (! is_array ( $_FILES )) {
			return 0;
		}
		foreach ( $_FILES as $file ) {
			if (isset ( $file ["error"] )) {
				if (! is_array ( $file ["error"] )) {
					if (! $file ["error"] || ! $onlySuccessful) {
						$numberFiles ++;
					}
				}
				if (is_array ( $file ["error"] )) {
					$numberFiles += $this->hasFileHelper ( $file ["error"], $onlySuccessful );
				}
			}
		}
		return $numberFiles;
	}

	/**
	 * Recursively counts file in an array of files
	 */
	protected final function hasFileHelper($data, $onlySuccessful)
	{
		$numberFiles = 0;
		if (! is_array ( $data )) {
			return 1;
		}
		foreach ( $data as $value ) {
			if (! is_array ( $value )) {
				if (! $value || ! $onlySuccessful) {
					$numberFiles ++;
				}
			}
			if (is_array ( $value )) {
				$numberFiles += $this->hasFileHelper ( $value, $onlySuccessful );
			}
		}
		return $numberFiles;
	}

	/**
	 * 是否是ajax请求
	 *
	 * @return boolean
	 */
	public function isAjax()
	{
		return isset ( $_SERVER ["HTTP_X_REQUESTED_WITH"] ) && $_SERVER ["HTTP_X_REQUESTED_WITH"] === "XMLHttpRequest";
	}

	/**
	 * 是否是SOAP请求
	 *
	 * @return boolean
	 */
	public function isSoapRequested()
	{
		if (isset ( $_SERVER ["HTTP_SOAPACTION"] )) {
			return true;
		} else {
			$contentType = $this->getContentType ();
			if (! empty ( $contentType )) {
				return stripos ( $contentType, "application/soap+xml" );
			}
		}
		return false;
	}

	/**
	 * 检测是否是安全请求
	 *
	 * @return boolean
	 */
	public function isSecureRequest()
	{
		return $this->getScheme () === "https";
	}

	/**
	 * 检测是否是指定的Http请求
	 *
	 * @param string|array methods
	 * @return boolean
	 */
	public function isMethod($methods)
	{
		$httpMethod = $this->getMethod ();

		if (is_string ( $methods )) {
			return $methods == $httpMethod;
		} elseif (is_array ( $methods )) {
			foreach ( $methods as $method ) {
				if ($method == $httpMethod) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * 是否是 POST 请求
	 *
	 * @return boolean
	 */
	public function isPost()
	{
		return $this->getMethod () === "POST";
	}

	/**
	 *
	 * 是否是GET请求
	 *
	 * @return boolean
	 */
	public function isGet()
	{
		return $this->getMethod () === "GET";
	}

	/**
	 * 是否是 PUT 请求
	 *
	 * @return boolean
	 */
	public function isPut()
	{
		return $this->getMethod () === "PUT";
	}

	/**
	 * 是否是 PATCH 请求
	 *
	 * @return boolean
	 */
	public function isPatch()
	{
		return $this->getMethod () === "PATCH";
	}

	/**
	 * 是否是 HEAD 请求
	 *
	 * @return boolean
	 */
	public function isHead()
	{
		return $this->getMethod () === "HEAD";
	}

	/**
	 * 是否是HTTP DELETE请求
	 *
	 * @return boolean
	 */
	public function isDelete()
	{
		return $this->getMethod () === "DELETE";
	}

	/**
	 * 是否是Http OPTIONS 请求
	 *
	 * @return boolean
	 */
	public function isOptions()
	{
		return $this->getMethod () === "OPTIONS";
	}

	/**
	 * 返回是否是 PJAX 请求
	 *
	 * @return boolean whether this is a PJAX request
	 */
	public function isPjax()
	{
		return $this->isAjax () && ! empty ( $_SERVER ["HTTP_X_PJAX"] );
	}

	/**
	 * 返回是否是 Adobe Flash 或 Flex 请求
	 *
	 * @return boolean whether this is an Adobe Flash or Adobe Flex request.
	 */
	public function isFlash()
	{
		if (isset ( $_SERVER ["HTTP_USER_AGENT"] )) {
			if (stripos ( $_SERVER ["HTTP_USER_AGENT"], "Shockwave" ) !== false || stripos ( $_SERVER ["HTTP_USER_AGENT"], "Flash" ) !== false) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Smooth out $_FILES to have plain array with all files uploaded
	 */
	protected final function smoothFiles($names, $types, $tmp_names, $sizes, $errors, $prefix)
	{
		$files = [ ];
		foreach ( $names as $idx => $name ) {
			$p = $prefix . "." . $idx;
			if (is_string ( $name )) {
				$files [] = [ "name" => $name,"type" => $types [$idx],"tmp_name" => $tmp_names [$idx],"size" => $sizes [$idx],"error" => $errors [$idx],"key" => $p ];
			}
			if (is_array ( $name )) {
				$parentFiles = $this->smoothFiles ( $names [$idx], $types [$idx], $tmp_names [$idx], $sizes [$idx], $errors [$idx], $p );
				foreach ( $parentFiles as $file ) {
					$files [] = $file;
				}
			}
		}
		return $files;
	}

	/**
	 * Process a request header and return an array of values with their qualities
	 */
	protected final function _getQualityHeader($serverIndex, $name)
	{
		$returnedParts = [ ];
		foreach ( preg_split ( "/,\\s*/", $this->getServer ( $serverIndex ), - 1, PREG_SPLIT_NO_EMPTY ) as $part ) {
			$headerParts = [ ];
			foreach ( preg_split ( "/\s*;\s*/", trim ( $part ), - 1, PREG_SPLIT_NO_EMPTY ) as $headerPart ) {
				if (strpos ( $headerPart, "=" ) !== false) {
					$split = explode ( "=", $headerPart, 2 );
					if ($split [0] === "q") {
						$headerParts ["quality"] = ( double ) $split [1];
					} else {
						$headerParts [$split [0]] = $split [1];
					}
				} else {
					$headerParts [$name] = $headerPart;
					$headerParts ["quality"] = 1.0;
				}
			}
			$returnedParts [] = $headerParts;
		}
		return $returnedParts;
	}

	/**
	 * Process a request header and return the one with best quality
	 */
	protected final function _getBestQuality($qualityParts, $name)
	{
		$i = 0;
		$quality = 0.0;
		$selectedName = "";
		foreach ( $qualityParts as $accept ) {
			if ($i == 0) {
				$quality = ( double ) $accept ["quality"];
				$selectedName = $accept [$name];
			} else {
				$acceptQuality = ( double ) $accept ["quality"];
				if ($acceptQuality > $quality) {
					$quality = $acceptQuality;
					$selectedName = $accept [$name];
				}
			}
			$i ++;
		}
		return $selectedName;
	}

	/**
	 * 解析当前请求URL的 URI 部分。
	 *
	 * @return string|boolean the request URI portion for the currently requested URL.
	 *         Note that the URI returned is URL-encoded.
	 * @throws InvalidConfigException if the request URI cannot be determined due to unusual server configuration
	 */
	protected function resolveRequestUri()
	{
		if (isset ( $_SERVER ["HTTP_X_REWRITE_URL"] )) {
			$requestUri = $_SERVER ["HTTP_X_REWRITE_URL"];
		} elseif (isset ( $_SERVER ["REQUEST_URI"] )) {
			$requestUri = $_SERVER ["REQUEST_URI"];
			if ($requestUri !== "" && substr ( $requestUri, 0, 1 ) !== "/") {
				$requestUri = preg_replace ( "/^(http|https):\/\/[^\/]+/i", "", $requestUri );
			}
		} elseif (isset ( $_SERVER ["ORIG_PATH_INFO"] ) && ! empty ( $_SERVER ["QUERY_STRING"] )) { // IIS 5.0 CGI
			$requestUri .= "?" . $_SERVER ["QUERY_STRING"];
		} else {
			throw new InvalidConfigException ( "Unable to determine the request URI." );
		}
		return $requestUri;
	}

	/**
	 * 解析当前URL的路径信息
	 *
	 * @return string part of the request URL that is after the entry script and before the question mark.
	 *         Note, the returned path info is decoded.
	 * @throws InvalidConfigException if the path info cannot be determined due to unexpected server configuration
	 */
	protected function resolvePathInfo()
	{
		$pathInfo = $this->getUrl ();

		if (($pos = strpos ( $pathInfo, '?' )) !== false) {
			$pathInfo = substr ( $pathInfo, 0, $pos );
		}

		$pathInfo = urldecode ( $pathInfo );

		// try to encode in UTF8 if not so
		// http://w3.org/International/questions/qa-forms-utf-8.html
		if (! preg_match ( "%^(?:
            [\x09\x0A\x0D\x20-\x7E]              # ASCII
            | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
            | \xE0[\xA0-\xBF][\x80-\xBF]         # excluding overlongs
            | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
            | \xED[\x80-\x9F][\x80-\xBF]         # excluding surrogates
            | \xF0[\x90-\xBF][\x80-\xBF]{2}      # planes 1-3
            | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
            | \xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16
            )*$%xs", $pathInfo )) {
            $pathInfo = utf8_encode ( $pathInfo );
		}

		$scriptUrl = $this->getScriptUrl ();
		$baseUrl = $this->getBaseUrl ();
		if (strpos ( $pathInfo, $scriptUrl ) === 0) {
			$pathInfo = substr ( $pathInfo, strlen ( $scriptUrl ) );
		} elseif ($baseUrl === '' || strpos ( $pathInfo, $baseUrl ) === 0) {
			$pathInfo = substr ( $pathInfo, strlen ( $baseUrl ) );
		} elseif (isset ( $_SERVER ['PHP_SELF'] ) && strpos ( $_SERVER ['PHP_SELF'], $scriptUrl ) === 0) {
			$pathInfo = substr ( $_SERVER ['PHP_SELF'], strlen ( $scriptUrl ) );
		} else {
			throw new InvalidConfigException ( 'Unable to determine the path info of the current request.' );
		}

		if ($pathInfo [0] === '/') {
			$pathInfo = substr ( $pathInfo, 1 );
		}

		return ( string ) $pathInfo;
	}
}