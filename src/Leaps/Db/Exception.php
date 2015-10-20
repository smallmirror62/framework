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
namespace Leaps\Db;

class Exception extends \Leaps\Core\Exception {

	/**
	 * PDO异常提供的错误信息
	 * @var array
	 * by [PDO::errorInfo](http://www.php.net/manual/en/pdo.errorinfo.php).
	 */
	public $errorInfo = [];

	/**
	 * 构造方法
	 * @param string $message PDO错误消息
	 * @param array $errorInfo PDO 错误详细
	 * @param integer $code PDO 错误代码
	 * @param \Exception $previous The previous exception used for the exception chaining.
	 */
	public function __construct($message, $errorInfo = [], $code = 0, \Exception $previous = null)
	{
		$this->errorInfo = $errorInfo;
		parent::__construct($message, $code, $previous);
	}

	/**
	 * 返回友好的异常名称
	 * @return string
	 */
	public function getName()
	{
		return 'Database Exception';
	}


	/**
	 * 异常的可读表示
	 * @return string
	 */
	public function __toString()
	{
		return parent::__toString() . PHP_EOL
		. 'Additional Information:' . PHP_EOL . print_r($this->errorInfo, true);
	}
}