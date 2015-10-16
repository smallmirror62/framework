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

use PDO, PDOStatement;
use Leaps\Di\Injectable;

class Connection extends Injectable
{

	/**
	 * 原始的PDO实例
	 *
	 * @var PDO
	 */
	public $pdo;

	/**
	 * 数据库连接配置
	 *
	 * @var array
	 */
	public $config;

	/**
	 * 该连接的数据库查询语法实例
	 *
	 * @var Query\Grammars\Grammar
	 */
	protected $grammar;

	/**
	 * 事件触发器
	 *
	 * @var \Leaps\Events\Dispatcher|string
	 */
	private $event = 'event';

	/**
	 * 所有已经执行的查询(所有连接)
	 *
	 * @var array
	 */
	public static $queries = [ ];

	/**
	 * 创建一个新的数据库连接实例
	 *
	 * @param PDO $pdo
	 * @param array $config 数据库连接配置
	 * @return void
	 */
	public function __construct(\PDO $pdo, $config)
	{
		$this->pdo = $pdo;
		$this->config = $config;
	}

	/**
	 * 开始一个链式查询
	 *
	 * <code>
	 * // Start a fluent query against the "users" table
	 * $query = DB::connection()->table('users');
	 *
	 * // Start a fluent query against the "users" table and get all the users
	 * $users = DB::connection()->table('users')->get();
	 * </code>
	 *
	 * @param string $table
	 * @return Query
	 */
	public function table($table)
	{
		return new Query ( $this, $this->grammar (), $table );
	}

	/**
	 * 为连接创建新的查询语法
	 *
	 * @return Query\Grammars\Grammar
	 */
	protected function grammar()
	{
		if (isset ( $this->grammar )) {
			return $this->grammar;
		}
		if (isset ( \Leaps\Db\Db::$registrar [$this->driver ()] )) {
			return $this->grammar = new \Leaps\Db\Db::$registrar [$this->driver ()] ['query'] ();
		}
		switch ($this->driver ()) {
			case 'mysql' :
				return $this->grammar = new Query\Grammar\MySQL ( $this );

			case 'sqlite' :
				return $this->grammar = new Query\Grammar\SQLite ( $this );

			case 'sqlsrv' :
				return $this->grammar = new Query\Grammar\SQLServer ( $this );

			case 'pgsql' :
				return $this->grammar = new Query\Grammar\Postgres ( $this );

			default :
				return $this->grammar = new Query\Grammar\Grammar ( $this );
		}
	}

	/**
	 * 执行数据库事务
	 *
	 * @param callback $callback
	 * @return bool
	 */
	public function transaction($callback)
	{
		$this->pdo->beginTransaction ();
		try {
			call_user_func ( $callback );
		} catch ( \Exception $e ) {
			$this->pdo->rollBack ();
			throw $e;
		}
		return $this->pdo->commit ();
	}

	/**
	 * 从查询结果返回所有行
	 *
	 * @param \PDOStatement $statement
	 * @param int $style
	 * @return array
	 */
	protected function fetch($statement, $style)
	{
		if ($style === PDO::FETCH_CLASS) {
			return $statement->fetchAll ( PDO::FETCH_CLASS, 'stdClass' );
		} else {
			return $statement->fetchAll ( $style );
		}
	}

	/**
	 * 记录查询日志和触发核心查询事件
	 *
	 * @param string $sql
	 * @param array $bindings
	 * @param int $start
	 * @return void
	 */
	protected function log($sql, $bindings, $start)
	{
		$time = number_format ( (microtime ( true ) - $start) * 1000, 2 );
		if (! is_object ( $this->event )) {
			$this->event = $this->_dependencyInjector->getShared ( $this->event );
		}
		$this->event->trigger ( 'leaps.query', [
				$sql,
				$bindings,
				$time
		] );
		static::$queries [] = compact ( 'sql', 'bindings', 'time' );
	}

	/**
	 * 获取数据库连接驱动名称
	 *
	 * @return string
	 */
	public function driver()
	{
		return $this->config ['driver'];
	}

	/**
	 * 魔术方法，开始动态查询数据库表
	 */
	public function __call($method, $parameters)
	{
		return $this->table ( $method );
	}
}