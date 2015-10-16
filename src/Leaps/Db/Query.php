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

use Closure;
use Leaps\Paginator;
use Leaps\Db\Query\Grammar\Postgres;
use Leaps\Db\Query\Grammar\SQLServer;

class Query
{

	/**
	 * 数据库连接
	 *
	 * @var \Leaps\Db\Connection
	 */
	public $connection;

	/**
	 * 查询语法实例
	 *
	 * @var \Leaps\Db\Query\Grammar\Grammar
	 */
	public $grammar;

	/**
	 * 查询容器
	 *
	 * @var array
	 */
	public $selects;

	/**
	 * 聚集列和函数
	 *
	 * @var array
	 */
	public $aggregate;

	/**
	 * 设定查询是否返回不同的结果。
	 *
	 * @var bool
	 */
	public $distinct = false;

	/**
	 * 表名称
	 *
	 * @var string
	 */
	public $from;

	/**
	 * 表联接
	 *
	 * @var array
	 */
	public $joins;

	/**
	 * 查询条件
	 *
	 * @var array
	 */
	public $wheres;

	/**
	 * 查询分组
	 *
	 * @var array
	 */
	public $groupings;

	/**
	 * HAVING 子句
	 *
	 * @var array
	 */
	public $havings;

	/**
	 * ORDER BY 子句
	 *
	 * @var array
	 */
	public $orderings;

	/**
	 * LIMIT 值
	 *
	 * @var int
	 */
	public $limit;

	/**
	 * OFFSET 值
	 *
	 * @var int
	 */
	public $offset;

	/**
	 * 查询值绑定
	 *
	 * @var array
	 */
	public $bindings = [ ];

	/**
	 * 构造方法
	 *
	 * @param Connection $connection
	 * @param Grammar $grammar
	 * @param string $table
	 * @return void
	 */
	public function __construct(\Leaps\Db\Connection $connection, \Leaps\Db\Query\Grammar\Grammar $grammar, $table)
	{
		$this->from = $table;
		$this->grammar = $grammar;
		$this->connection = $connection;
	}

	/**
	 * 强制返回不同的查询结果
	 *
	 * @return Query
	 */
	public function distinct()
	{
		$this->distinct = true;
		return $this;
	}

	/**
	 * Add an array of columns to the SELECT clause.
	 *
	 * @param array $columns
	 * @return Query
	 */
	public function select($columns = ['*'])
	{
		$this->selects = $columns;
		return $this;
	}

	/**
	 * Add a join clause to the query.
	 *
	 * @param string $table
	 * @param string $column1
	 * @param string $operator
	 * @param string $column2
	 * @param string $type
	 * @return Query
	 */
	public function join($table, $column1, $operator = null, $column2 = null, $type = 'INNER')
	{
		if ($column1 instanceof Closure) {
			$this->joins [] = new Query\Join ( $type, $table );
			call_user_func ( $column1, end ( $this->joins ) );
		} else {
			$join = new Query\Join ( $type, $table );
			$join->on ( $column1, $operator, $column2 );
			$this->joins [] = $join;
		}
		return $this;
	}

	/**
	 * Add a left join to the query.
	 *
	 * @param string $table
	 * @param string $column1
	 * @param string $operator
	 * @param string $column2
	 * @return Query
	 */
	public function leftJoin($table, $column1, $operator = null, $column2 = null)
	{
		return $this->join ( $table, $column1, $operator, $column2, 'LEFT' );
	}

	/**
	 * 重置Where查询条件
	 *
	 * @return void
	 */
	public function resetWhere()
	{
		list ( $this->wheres, $this->bindings ) = [ [ ],[ ] ];
	}

	/**
	 * Add a raw where condition to the query.
	 *
	 * @param string $where
	 * @param array $bindings
	 * @param string $connector
	 * @return Query
	 */
	public function rawWhere($where, $bindings = [], $connector = 'AND')
	{
		$this->wheres [] = [ 'type' => 'whereRaw','connector' => $connector,'sql' => $where ];
		$this->bindings = array_merge ( $this->bindings, $bindings );
		return $this;
	}

	/**
	 * Add a raw or where condition to the query.
	 *
	 * @param string $where
	 * @param array $bindings
	 * @return Query
	 */
	public function rawOrWhere($where, $bindings = [])
	{
		return $this->rawWhere ( $where, $bindings, 'OR' );
	}

	/**
	 * Add a where condition to the query.
	 *
	 * @param string $column
	 * @param string $operator
	 * @param mixed $value
	 * @param string $connector
	 * @return Query
	 */
	public function where($column, $operator = null, $value = null, $connector = 'AND')
	{
		if ($column instanceof Closure) {
			return $this->whereNested ( $column, $connector );
		}
		$type = 'where';
		$this->wheres [] = compact ( 'type', 'column', 'operator', 'value', 'connector' );
		$this->bindings [] = $value;
		return $this;
	}

	/**
	 * Add an or where condition to the query.
	 *
	 * @param string $column
	 * @param string $operator
	 * @param mixed $value
	 * @return Query
	 */
	public function orWhere($column, $operator = null, $value = null)
	{
		return $this->where ( $column, $operator, $value, 'OR' );
	}

	/**
	 * Add an or where condition for the primary key to the query.
	 *
	 * @param mixed $value
	 * @return Query
	 */
	public function orWhereID($value)
	{
		return $this->orWhere ( 'id', '=', $value );
	}

	/**
	 * Add a where in condition to the query.
	 *
	 * @param string $column
	 * @param array $values
	 * @param string $connector
	 * @param bool $not
	 * @return Query
	 */
	public function whereIn($column, $values, $connector = 'AND', $not = false)
	{
		$type = ($not) ? 'whereNotIn' : 'whereIn';
		$this->wheres [] = compact ( 'type', 'column', 'values', 'connector' );
		$this->bindings = array_merge ( $this->bindings, $values );
		return $this;
	}

	/**
	 * Add an or where in condition to the query.
	 *
	 * @param string $column
	 * @param array $values
	 * @return Query
	 */
	public function orWhereIn($column, $values)
	{
		return $this->whereIn ( $column, $values, 'OR' );
	}

	/**
	 * Add a where not in condition to the query.
	 *
	 * @param string $column
	 * @param array $values
	 * @param string $connector
	 * @return Query
	 */
	public function whereNotIn($column, $values, $connector = 'AND')
	{
		return $this->whereIn ( $column, $values, $connector, true );
	}

	/**
	 * Add an or where not in condition to the query.
	 *
	 * @param string $column
	 * @param array $values
	 * @return Query
	 */
	public function orWhereNotIn($column, $values)
	{
		return $this->whereNotIn ( $column, $values, 'OR' );
	}

	/**
	 * Add a BETWEEN condition to the query
	 *
	 * @param string $column
	 * @param mixed $min
	 * @param mixed $max
	 * @param string $connector
	 * @param boolean $not
	 * @return Query
	 */
	public function whereBetween($column, $min, $max, $connector = 'AND', $not = false)
	{
		$type = ($not) ? 'whereNotBetween' : 'whereBetween';
		$this->wheres [] = compact ( 'type', 'column', 'min', 'max', 'connector' );
		$this->bindings [] = $min;
		$this->bindings [] = $max;
		return $this;
	}

	/**
	 * Add a OR BETWEEN condition to the query
	 *
	 * @param string $column
	 * @param mixed $min
	 * @param mixed $max
	 * @return Query
	 */
	public function orWhereBetween($column, $min, $max)
	{
		return $this->whereBetween ( $column, $min, $max, 'OR' );
	}

	/**
	 * Add a NOT BETWEEN condition to the query
	 *
	 * @param string $column
	 * @param mixed $min
	 * @param mixed $max
	 * @return Query
	 */
	public function whereNotBetween($column, $min, $max, $connector = 'AND')
	{
		return $this->whereBetween ( $column, $min, $max, $connector, true );
	}

	/**
	 * Add a OR NOT BETWEEN condition to the query
	 *
	 * @param string $column
	 * @param mixed $min
	 * @param mixed $max
	 * @return Query
	 */
	public function orWhereNotBetween($column, $min, $max)
	{
		return $this->whereNotBetween ( $column, $min, $max, 'OR' );
	}

	/**
	 * Add a where null condition to the query.
	 *
	 * @param string $column
	 * @param string $connector
	 * @param bool $not
	 * @return Query
	 */
	public function whereNull($column, $connector = 'AND', $not = false)
	{
		$type = ($not) ? 'whereNotNull' : 'whereNull';
		$this->wheres [] = compact ( 'type', 'column', 'connector' );
		return $this;
	}

	/**
	 * Add an or where null condition to the query.
	 *
	 * @param string $column
	 * @return Query
	 */
	public function orWhereNull($column)
	{
		return $this->whereNull ( $column, 'OR' );
	}

	/**
	 * Add a where not null condition to the query.
	 *
	 * @param string $column
	 * @param string $connector
	 * @return Query
	 */
	public function whereNotNull($column, $connector = 'AND')
	{
		return $this->whereNull ( $column, $connector, true );
	}

	/**
	 * Add an or where not null condition to the query.
	 *
	 * @param string $column
	 * @return Query
	 */
	public function orWhereNotNull($column)
	{
		return $this->whereNotNull ( $column, 'OR' );
	}

	/**
	 * Add a nested where condition to the query.
	 *
	 * @param Closure $callback
	 * @param string $connector
	 * @return Query
	 */
	public function whereNested($callback, $connector = 'AND')
	{
		$type = 'whereNested';
		$query = new Query ( $this->connection, $this->grammar, $this->from );
		call_user_func ( $callback, $query );
		if ($query->wheres !== null) {
			$this->wheres [] = compact ( 'type', 'query', 'connector' );
		}
		$this->bindings = array_merge ( $this->bindings, $query->bindings );
		return $this;
	}

	/**
	 * Add dynamic where conditions to the query.
	 *
	 * @param string $method
	 * @param array $parameters
	 * @return Query
	 */
	private function dynamicWhere($method, $parameters)
	{
		$finder = substr ( $method, 6 );
		$flags = PREG_SPLIT_DELIM_CAPTURE;
		$segments = preg_split ( '/(_and_|_or_)/i', $finder, - 1, $flags );
		$connector = 'AND';
		$index = 0;
		foreach ( $segments as $segment ) {
			if ($segment != '_and_' and $segment != '_or_') {
				$this->where ( $segment, '=', $parameters [$index], $connector );
				$index ++;
			} else {
				$connector = trim ( strtoupper ( $segment ), '_' );
			}
		}
		return $this;
	}

	/**
	 * Add a grouping to the query.
	 *
	 * @param string $column
	 * @return Query
	 */
	public function groupBy($column)
	{
		$this->groupings [] = $column;
		return $this;
	}

	/**
	 * Add a having to the query.
	 *
	 * @param string $column
	 * @param string $operator
	 * @param mixed $value
	 */
	public function having($column, $operator, $value)
	{
		$this->havings [] = compact ( 'column', 'operator', 'value' );
		$this->bindings [] = $value;
		return $this;
	}

	/**
	 * Add an ordering to the query.
	 *
	 * @param string $column
	 * @param string $direction
	 * @return Query
	 */
	public function orderBy($column, $direction = 'asc')
	{
		$this->orderings [] = compact ( 'column', 'direction' );
		return $this;
	}

	/**
	 * Set the query offset.
	 *
	 * @param int $value
	 * @return Query
	 */
	public function skip($value)
	{
		$this->offset = $value;
		return $this;
	}

	/**
	 * Set the query limit.
	 *
	 * @param int $value
	 * @return Query
	 */
	public function take($value)
	{
		$this->limit = $value;
		return $this;
	}

	/**
	 * Set the query limit and offset for a given page.
	 *
	 * @param int $page
	 * @param int $per_page
	 * @return Query
	 */
	public function forPage($page, $per_page)
	{
		return $this->skip ( ($page - 1) * $per_page )->take ( $per_page );
	}

	/**
	 * 查询主键
	 *
	 * @param int $id
	 * @param array $columns
	 * @return object
	 */
	public function find($id, $columns = ['*'])
	{
		return $this->where ( 'id', '=', $id )->first ( $columns );
	}

	/**
	 * 获取一列
	 *
	 * @param string $column
	 * @return mixed
	 */
	public function only($column)
	{
		$sql = $this->grammar->select ( $this->select ( [ $column ] ) );
		return $this->connection->only ( $sql, $this->bindings );
	}

	/**
	 * 获取一条数据
	 *
	 * @param array $columns
	 * @return mixed
	 */
	public function first($columns = ['*'])
	{
		$columns = ( array ) $columns;
		$results = $this->take ( 1 )->get ( $columns );
		return (count ( $results ) > 0) ? $results [0] : null;
	}

	/**
	 * Get an array with the values of a given column.
	 *
	 * @param string $column
	 * @param string $key
	 * @return array
	 */
	public function lists($column, $key = null)
	{
		$columns = (is_null ( $key )) ? [ $column ] : [ $column,$key ];
		$results = $this->get ( $columns );
		$values = array_map ( function ($row) use($column)
		{
			return $row->$column;
		}, $results );
		if (! is_null ( $key ) && count ( $results )) {
			return array_combine ( array_map ( function ($row) use($key)
			{
				return $row->$key;
			}, $results ), $values );
		}
		return $values;
	}

	/**
	 * 获取查询结果实例
	 *
	 * @param array $columns
	 * @return array
	 */
	public function get($columns = ['*'])
	{
		if (is_null ( $this->selects ))
			$this->select ( $columns );
		$sql = $this->grammar->select ( $this );
		$results = $this->connection->query ( $sql, $this->bindings );
		if ($this->offset > 0 and $this->grammar instanceof SQLServer) {
			array_walk ( $results, function ($result)
			{
				unset ( $result->rownum );
			} );
		}
		$this->selects = null;
		return $results;
	}

	/**
	 * 获取字段的合
	 *
	 * @param string $aggregator
	 * @param array $columns
	 * @return mixed
	 */
	public function aggregate($aggregator, $columns)
	{
		$this->aggregate = compact ( 'aggregator', 'columns' );
		$sql = $this->grammar->select ( $this );
		$result = $this->connection->only ( $sql, $this->bindings );
		$this->aggregate = null;
		return $result;
	}

	/**
	 * 获取分页
	 *
	 * @param int $per_page
	 * @param array $columns
	 * @return Paginator
	 */
	public function paginate($per_page = 20, $columns = ['*'])
	{
		list ( $orderings, $this->orderings ) = [ $this->orderings,null ];
		$total = $this->count ( reset ( $columns ) );
		$page = Paginator::page ( $total, $per_page );
		$this->orderings = $orderings;
		$results = $this->for_page ( $page, $per_page )->get ( $columns );
		return Paginator::make ( $results, $total, $per_page );
	}

	/**
	 * 插入数据
	 *
	 * @param array $values
	 * @return bool
	 */
	public function insert($values)
	{
		if (! is_array ( reset ( $values ) )) {
			$values = [ $values ];
		}
		$bindings = [ ];
		foreach ( $values as $value ) {
			$bindings = array_merge ( $bindings, array_values ( $value ) );
		}
		$sql = $this->grammar->insert ( $this, $values );
		return $this->connection->query ( $sql, $bindings );
	}

	/**
	 * 获取插入的ID
	 *
	 * @param array $values
	 * @param string $column
	 * @return mixed
	 */
	public function insertGetID($values, $column = 'id')
	{
		$sql = $this->grammar->insertGetID ( $this, $values, $column );
		$result = $this->connection->query ( $sql, array_values ( $values ) );
		if (isset ( $values [$column] )) {
			return $values [$column];
		} else if ($this->grammar instanceof Postgres) {
			$row = ( array ) $result [0];
			return ( int ) $row [$column];
		} else {
			return ( int ) $this->connection->pdo->lastInsertId ();
		}
	}

	/**
	 * 字段加一
	 *
	 * @param string $column
	 * @param int $amount
	 * @return int
	 */
	public function increment($column, $amount = 1)
	{
		return $this->adjust ( $column, $amount, ' + ' );
	}

	/**
	 * 字段减一
	 *
	 * @param string $column
	 * @param int $amount
	 * @return int
	 */
	public function decrement($column, $amount = 1)
	{
		return $this->adjust ( $column, $amount, ' - ' );
	}

	/**
	 * Adjust the value of a column up or down by a given amount.
	 *
	 * @param string $column
	 * @param int $amount
	 * @param string $operator
	 * @return int
	 */
	protected function adjust($column, $amount, $operator)
	{
		$wrapped = $this->grammar->wrap ( $column );
		$value = new Expression ( $wrapped . $operator . $amount );
		return $this->update ( [ $column => $value ] );
	}

	/**
	 * Update an array of values in the database table.
	 *
	 * @param array $values
	 * @return int
	 */
	public function update($values)
	{
		$bindings = array_merge ( array_values ( $values ), $this->bindings );
		$sql = $this->grammar->update ( $this, $values );
		return $this->connection->query ( $sql, $bindings );
	}

	/**
	 * 执行一个删除查询
	 *
	 * 或者指定ID
	 *
	 * @param int $id
	 * @return int
	 */
	public function delete($id = null)
	{
		if (! is_null ( $id )) {
			$this->where ( 'id', '=', $id );
		}
		$sql = $this->grammar->delete ( $this );
		return $this->connection->query ( $sql, $this->bindings );
	}

	/**
	 * 魔术方法，实现动态查询
	 */
	public function __call($method, $parameters)
	{
		if (strpos ( $method, 'where' ) === 0) {
			return $this->dynamicWhere ( $method, $parameters, $this );
		}
		if (in_array ( $method, [ 'count','min','max','avg','sum' ] )) {
			if (count ( $parameters ) == 0)
				$parameters [0] = '*';
			return $this->aggregate ( strtoupper ( $method ), ( array ) $parameters [0] );
		}
		throw new \Leaps\Db\Exception ( "Method [$method] is not defined on the Query class." );
	}
}