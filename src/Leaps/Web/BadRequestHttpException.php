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
namespace Leaps\Web;

class BadRequestHttpException extends HttpException
{
	/**
	 * 构造方法
	 *
	 * @param string $message 错误消息
	 * @param integer $code 错误代码
	 * @param \Exception $previous The previous exception used for the exception chaining.
	 */
	public function __construct($message = null, $code = 0, \Exception $previous = null)
	{
		parent::__construct ( 400, $message, $code, $previous );
	}
}