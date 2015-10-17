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
namespace Leaps\Db\Query\Grammar;

use Leaps\Db\Query;

class Grammar extends \Leaps\Db\Grammar
{

	/**
	 * 保存的时间戳格式
	 *
	 * @var string
	 */
	public $datetime = 'Y-m-d H:i:s';

	/**
	 * 所有查询组件
	 *
	 * @var array
	 */
	protected $components = [ 'aggregate','selects','from','joins','wheres','groupings','havings','orderings','limit','offset' ];

	/**
	 * Compile a SQL SELECT statement from a Query instance.
	 *
	 * @param Query $query
	 * @return string
	 */
	public function select(Query $query)
	{
		return $this->concatenate ( $this->components ( $query ) );
	}

	/**
	 * 生成的SQL查询的每一部分
	 *
	 * @param Query $query
	 * @return array
	 */
	final protected function components($query)
	{
		foreach ( $this->components as $component ) {
			if (! is_null ( $query->$component )) {
				$sql [$component] = call_user_func ( [$this,$component], $query );
			}
		}
		return ( array ) $sql;
	}

	/**
	 * Concatenate an array of SQL segments, removing those that are empty.
	 *
	 * @param array $components
	 * @return string
	 */
	final protected function concatenate($components)
	{
		return implode ( ' ', array_filter ( $components, function ($value)
		{
			return ( string ) $value !== '';
		} ) );
	}

	/**
	 * 编译 SELECT 子句
	 *
	 * @param Query $query
	 * @return string
	 */
	protected function selects(Query $query)
	{
		if (! is_null ( $query->aggregate )) {
			return;
		}
		$select = ($query->distinct) ? 'SELECT DISTINCT ' : 'SELECT ';
		return $select . $this->columnize ( $query->selects );
	}

	/**
	 * 编译聚合 SELECT 子句
	 *
	 * @param Query $query
	 * @return string
	 */
	protected function aggregate(Query $query)
	{
		$column = $this->columnize ( $query->aggregate ['columns'] );
		if ($query->distinct and $column !== '*') {
			$column = 'DISTINCT ' . $column;
		}
		return 'SELECT ' . $query->aggregate ['aggregator'] . '(' . $column . ') AS ' . $this->wrap ( 'aggregate' );
	}

	/**
	 * 编译 FROM 子句
	 *
	 * @param Query $query
	 * @return string
	 */
	protected function from(Query $query)
	{
		return 'FROM ' . $this->wrapTable ( $query->from );
	}

	/**
	 * 编译JOIN子句
	 *
	 * @param Query $query
	 * @return string
	 */
	protected function joins(Query $query)
	{
		foreach ( $query->joins as $join ) {
			$table = $this->wrapTable ( $join->table );
			$clauses = array ();
			foreach ( $join->clauses as $clause ) {
				extract ( $clause );
				$column1 = $this->wrap ( $column1 );
				$column2 = $this->wrap ( $column2 );
				$clauses [] = "{$connector} {$column1} {$operator} {$column2}";
			}
			$search = array ('AND ','OR ' );
			$clauses [0] = str_replace ( $search, '', $clauses [0] );
			$clauses = implode ( ' ', $clauses );
			$sql [] = "{$join->type} JOIN {$table} ON {$clauses}";
		}
		return implode ( ' ', $sql );
	}

	/**
	 * 编译查询WHERE
	 *
	 * @param Query $query
	 * @return string
	 */
	final protected function wheres(Query $query)
	{
		if (is_null ( $query->wheres )) {
			return '';
		}
		foreach ( $query->wheres as $where ) {
			$sql [] = $where ['connector'] . ' ' . $this->{$where ['type']} ( $where );
		}
		if (isset ( $sql )) {
			return 'WHERE ' . preg_replace ( '/AND |OR /', '', implode ( ' ', $sql ), 1 );
		}
	}

	/**
	 * 编译嵌套 WHERE 子句
	 *
	 * @param array $where
	 * @return string
	 */
	protected function whereNested($where)
	{
		return '(' . substr ( $this->wheres ( $where ['query'] ), 6 ) . ')';
	}

	/**
	 * 编译标准 WHERE 子句
	 *
	 * @param array $where
	 * @return string
	 */
	protected function where($where)
	{
		$parameter = $this->parameter ( $where ['value'] );
		return $this->wrap ( $where ['column'] ) . ' ' . $where ['operator'] . ' ' . $parameter;
	}

	/**
	 * 编译 WHERE IN 子句
	 *
	 * @param array $where
	 * @return string
	 */
	protected function whereIn($where)
	{
		$parameters = $this->parameterize ( $where ['values'] );
		return $this->wrap ( $where ['column'] ) . ' IN (' . $parameters . ')';
	}

	/**
	 * 编译 WHERE NOT IN 子句
	 *
	 * @param array $where
	 * @return string
	 */
	protected function whereNotIn($where)
	{
		$parameters = $this->parameterize ( $where ['values'] );
		return $this->wrap ( $where ['column'] ) . ' NOT IN (' . $parameters . ')';
	}

	/**
	 * 编译 WHERE BETWEEN 子句
	 *
	 * @param array $where
	 * @return string
	 */
	protected function whereBetween($where)
	{
		$min = $this->parameter ( $where ['min'] );
		$max = $this->parameter ( $where ['max'] );
		return $this->wrap ( $where ['column'] ) . ' BETWEEN ' . $min . ' AND ' . $max;
	}

	/**
	 * 编译 WHERE NOT BETWEEN 子句
	 *
	 * @param array $where
	 * @return string
	 */
	protected function whereNotBetween($where)
	{
		$min = $this->parameter ( $where ['min'] );
		$max = $this->parameter ( $where ['max'] );
		return $this->wrap ( $where ['column'] ) . ' NOT BETWEEN ' . $min . ' AND ' . $max;
	}

	/**
	 * 编译 WHERE NULL 子句
	 *
	 * @param array $where
	 * @return string
	 */
	protected function whereNull($where)
	{
		return $this->wrap ( $where ['column'] ) . ' IS NULL';
	}

	/**
	 * 编译 WHERE NULL 子句
	 *
	 * @param array $where
	 * @return string
	 */
	protected function whereNotNull($where)
	{
		return $this->wrap ( $where ['column'] ) . ' IS NOT NULL';
	}

	/**
	 * 编译 原始SQL 子句
	 *
	 * @param array $where
	 * @return string
	 */
	final protected function whereRaw($where)
	{
		return $where ['sql'];
	}

	/**
	 * 编译 GROUP BY 子句
	 *
	 * @param Query $query
	 * @return string
	 */
	protected function groupings(Query $query)
	{
		return 'GROUP BY ' . $this->columnize ( $query->groupings );
	}

	/**
	 * 编译 HAVING 子句
	 *
	 * @param Query $query
	 * @return string
	 */
	protected function havings(Query $query)
	{
		if (is_null ( $query->havings ))
			return '';
		foreach ( $query->havings as $having ) {
			$sql [] = 'AND ' . $this->wrap ( $having ['column'] ) . ' ' . $having ['operator'] . ' ' . $this->parameter ( $having ['value'] );
		}
		return 'HAVING ' . preg_replace ( '/AND /', '', implode ( ' ', $sql ), 1 );
	}

	/**
	 * 编译 ORDER BY 子句
	 *
	 * @param Query $query
	 * @return string
	 */
	protected function orderings(Query $query)
	{
		foreach ( $query->orderings as $ordering ) {
			$sql [] = $this->wrap ( $ordering ['column'] ) . ' ' . strtoupper ( $ordering ['direction'] );
		}
		return 'ORDER BY ' . implode ( ', ', $sql );
	}

	/**
	 * 编译 LIMIT 子句
	 *
	 * @param Query $query
	 * @return string
	 */
	protected function limit(Query $query)
	{
		return 'LIMIT ' . $query->limit;
	}

	/**
	 * 编译 OFFSET 子句
	 *
	 * @param Query $query
	 * @return string
	 */
	protected function offset(Query $query)
	{
		return 'OFFSET ' . $query->offset;
	}

	/**
	 * 编译插入的SQL查询语句实例
	 *
	 * @param Query $query
	 * @param array $values
	 * @return string
	 */
	public function insert(Query $query, $values)
	{
		$table = $this->wrapTable ( $query->from );
		if (! is_array ( reset ( $values ) ))
			$values = [ $values ];
		$columns = $this->columnize ( array_keys ( reset ( $values ) ) );
		$parameters = $this->parameterize ( reset ( $values ) );
		$parameters = implode ( ', ', array_fill ( 0, count ( $values ), "($parameters)" ) );
		return "INSERT INTO {$table} ({$columns}) VALUES {$parameters}";
	}

	/**
	 * 编译插入的SQL查询语句实例并获取主键ID
	 *
	 * @param \Leaps\Db\Query $query
	 * @param array $values
	 * @param string $column
	 * @return string
	 */
	public function insertGetID(Query $query, $values, $column)
	{
		return $this->insert ( $query, $values );
	}

	/**
	 * 编译更新的SQL查询语句实例
	 *
	 * @param \Leaps\Db\Query $query
	 * @param array $values
	 * @return string
	 */
	public function update(Query $query, $values)
	{
		$table = $this->wrapTable ( $query->from );
		foreach ( $values as $column => $value ) {
			$columns [] = $this->wrap ( $column ) . ' = ' . $this->parameter ( $value );
		}
		$columns = implode ( ', ', $columns );
		return trim ( "UPDATE {$table} SET {$columns} " . $this->wheres ( $query ) );
	}

	/**
	 * 编译删除的SQL查询语句实例。
	 *
	 * @param \Leaps\Db\Query $query
	 * @return string
	 */
	public function delete(Query $query)
	{
		$table = $this->wrapTable ( $query->from );
		return trim ( "DELETE FROM {$table} " . $this->wheres ( $query ) );
	}

	/**
	 * Transform an SQL short-cuts into real SQL for PDO.
	 *
	 * @param string $sql
	 * @param array $bindings
	 * @return string
	 */
	public function shortcut($sql, &$bindings)
	{
		if (strpos ( $sql, '(...)' ) !== false) {
			for($i = 0; $i < count ( $bindings ); $i ++) {
				if (is_array ( $bindings [$i] )) {
					$parameters = $this->parameterize ( $bindings [$i] );
					array_splice ( $bindings, $i, 1, $bindings [$i] );
					$sql = preg_replace ( '~\(\.\.\.\)~', "({$parameters})", $sql, 1 );
				}
			}
		}
		return trim ( $sql );
	}
}