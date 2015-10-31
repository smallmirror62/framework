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
namespace Leaps\Http\Client;

use Leaps\Core\Base;

class Response extends Base
{

	/**
	 * 原始的响应
	 *
	 * @var array
	 */
	public $responseData;

	/**
	 * 响应的Header
	 *
	 * @var \Leaps\Http\Headers
	 */
	protected $header;

	/**
	 * 请求所消耗的时间
	 *
	 * @var int $time
	 */
	protected $time = 0;

	/**
	 * 初始化
	 */
	public function init()
	{
		if (isset ( $this->responseData ['code'] ))
			$this->httpCode = $this->responseData ['code'];
		if (isset ( $this->responseData ['time'] ))
			$this->time = $this->responseData ['time'];
		if (isset ( $this->responseData ['data'] ))
			$this->body = $this->responseData ['data'];

		if (isset ( $this->responseData ['header'] ) && is_array ( $this->responseData ['header'] ))
			foreach ( $this->responseData ['header'] as $item ) {
				if (preg_match ( '#^([a-zA-Z0-9\-]+): (.*)$#', $item, $m )) {
					if ($m [1] == 'Set-Cookie') {
						if (preg_match ( '#^([a-zA-Z0-9\-_.]+)=(.*)$#', $m [2], $m2 )) {
							if (false !== ($pos = strpos ( $m2 [2], ';' ))) {
								$m2 [2] = substr ( $m2 [2], 0, $pos );
							}
							$this->cookies [$m2 [1]] = $m2 [2];
						}
					} else {
						$this->headers [$m [1]] = $m [2];
					}
				}
			}
	}

	/**
	 * 获取响应内容
	 *
	 * @return array
	 */
	public function getBody()
	{
		return $this->body;
	}

	/**
	 * 获取响应代码
	 *
	 * @return number
	 */
	public function getHttpCode()
	{
		return $this->httpCode;
	}
}