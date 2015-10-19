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
namespace Leaps\Db\Query;

class Join
{

	/**
	 * 类型
	 *
	 * @var string
	 */
	public $type;

	/**
	 * 表
	 *
	 * @var string
	 */
	public $table;

	/**
	 * 加入联合的子句
	 *
	 * @var array
	 */
	public $clauses = [ ];

	/**
	 * 创建一个新的联合查询实例
	 *
	 * @param string $type 联合类型
	 * @param string $table 联合的表
	 * @return void
	 */
	public function __construct($type, $table)
	{
		$this->type = $type;
		$this->table = $table;
	}

	/**
	 * Add an ON clause to the join.
	 *
	 * @param string $column1
	 * @param string $operator
	 * @param string $column2
	 * @param string $connector
	 * @return Join
	 */
	public function on($column1, $operator, $column2, $connector = 'AND')
	{
		$this->clauses [] = compact ( 'column1', 'operator', 'column2', 'connector' );
		return $this;
	}

	/**
	 * Add an OR ON clause to the join.
	 *
	 * @param string $column1
	 * @param string $operator
	 * @param string $column2
	 * @return Join
	 */
	public function orOn($column1, $operator, $column2)
	{
		return $this->on ( $column1, $operator, $column2, 'OR' );
	}
}