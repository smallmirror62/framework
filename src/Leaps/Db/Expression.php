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

class Expression {

	/**
	 * 数据库表达式的值
	 *
	 * @var string
	 */
	protected $value;

	/**
	 * 创建一个新的数据库表达式实例
	 *
	 * @param  string  $value
	 * @return void
	 */
	public function __construct($value)
	{
		$this->value = $value;
	}

	/**
	 * 从数据库表达式获取字符串
	 * @return string
	 */
	public function get()
	{
		return $this->value;
	}

	/**
	 * 魔术方法，从数据库表达式获取字符串
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->get();
	}

}