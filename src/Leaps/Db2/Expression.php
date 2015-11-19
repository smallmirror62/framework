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

/**
 * Expression represents a DB expression that does not need escaping or quoting.
 * When an Expression object is embedded within a SQL statement or fragment,
 * it will be replaced with the [[expression]] property value without any
 * DB escaping or quoting. For example,
 *
 * ~~~
 * $expression = new Expression('NOW()');
 * $sql = 'SELECT ' . $expression;  // SELECT NOW()
 * ~~~
 *
 * An expression can also be bound with parameters specified via [[params]].
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Expression extends \Leaps\base\Object
{
	/**
	 * @var string the DB expression
	 */
	public $expression;
	/**
	 * @var array list of parameters that should be bound for this expression.
	 * The keys are placeholders appearing in [[expression]] and the values
	 * are the corresponding parameter values.
	 */
	public $params = [];


	/**
	 * 构造方法
	 * @param string $expression the DB expression
	 * @param array $params parameters
	 * @param array $config name-value pairs that will be used to initialize the object properties
	 */
	public function __construct($expression, $params = [], $config = [])
	{
		$this->expression = $expression;
		$this->params = $params;
		parent::__construct($config);
	}

	/**
	 * 字符串魔术方法
	 * @return string the DB expression
	 */
	public function __toString()
	{
		return $this->expression;
	}
}