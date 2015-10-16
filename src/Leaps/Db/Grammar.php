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

abstract class Grammar
{

	/**
	 * 数据库系统的关键字标识符
	 *
	 * @var string
	 */
	protected $wrapper = '"%s"';

	/**
	 * 语法的数据库连接实例。
	 *
	 * @var Connection
	 */
	protected $connection;

	/**
	 * 创建新的数据库连接语法实例
	 *
	 * @param Connection $connection
	 * @return void
	 */
	public function __construct(Connection $connection)
	{
		$this->connection = $connection;
	}

	/**
	 * 用关键字标识符来包装一个表
	 *
	 * @param string $table
	 * @return string
	 */
	public function wrapTable($table)
	{
		if ($table instanceof Expression) {
			return $this->wrap ( $table );
		}
		$prefix = '';
		if (isset ( $this->connection->config ['prefix'] )) {
			$prefix = $this->connection->config ['prefix'];
		}
		return $this->wrap ( $prefix . $table );
	}

	/**
	 * 包装一个关键字标识符的值
	 *
	 * @param string $value
	 * @return string
	 */
	public function wrap($value)
	{
		if ($value instanceof Expression) {
			return $value->get ();
		}
		if (strpos ( strtolower ( $value ), ' as ' ) !== false) {
			$segments = explode ( ' ', $value );
			return sprintf ( '%s AS %s', $this->wrap ( $segments [0] ), $this->wrap ( $segments [2] ) );
		}
		$segments = explode ( '.', $value );
		foreach ( $segments as $key => $value ) {
			if ($key == 0 and count ( $segments ) > 1) {
				$wrapped [] = $this->wrapTable ( $value );
			} else {
				$wrapped [] = $this->wrapValue ( $value );
			}
		}
		return implode ( '.', $wrapped );
	}

	/**
	 * 用关键字标识符来包装一个值
	 *
	 * @param string $value
	 * @return string
	 */
	protected function wrapValue($value)
	{
		return ($value !== '*') ? sprintf ( $this->wrapper, $value ) : $value;
	}

	/**
	 * 从一个数组中创建查询参数
	 *
	 * <code>
	 * 返回 "?, ?, ?" PDO占位符
	 * $parameters = $grammar->parameterize(array(1, 2, 3));
	 *
	 * //返回 "?, "Taylor"" 表达式
	 * $parameters = $grammar->parameterize(array(1, DB::raw('Taylor')));
	 * </code>
	 *
	 * @param array $values
	 * @return string
	 */
	final public function parameterize($values)
	{
		return implode ( ', ', array_map ( [ $this, 'parameter' ], $values ) );
	}

	/**
	 * 从查询参数字符串获取一个值
	 *
	 * <code>
	 * // Returns a "?" PDO place-holder
	 * $value = $grammar->parameter('Taylor Otwell');
	 *
	 * // Returns "Taylor Otwell" as the raw value of the expression
	 * $value = $grammar->parameter(DB::raw('Taylor Otwell'));
	 * </code>
	 *
	 * @param mixed $value
	 * @return string
	 */
	final public function parameter($value)
	{
		return ($value instanceof Expression) ? $value->get () : '?';
	}

	/**
	 * 创建一个以逗号分隔的列表
	 *
	 * <code>
	 * // Returns ""Taylor", "Otwell"" when the identifier is quotes
	 * $columns = $grammar->columnize(array('Taylor', 'Otwell'));
	 * </code>
	 *
	 * @param array $columns
	 * @return string
	 */
	final public function columnize($columns)
	{
		return implode ( ', ', array_map ( [ $this, 'wrap' ], $columns ) );
	}
}