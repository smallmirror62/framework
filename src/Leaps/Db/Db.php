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

use PDO;
use Closure;
use Leaps\Kernel;
use Leaps\Core\Base;
use Leaps\Db\Expression;
use Leaps\Db\Connection;

class Db extends Base
{
	/**
	 * 开启数据库查询日志
	 *
	 * @var boolean
	 */
	public $profile = false;

	/**
	 * PDO 获取风格
	 *
	 * @var Ambiguous $fetch
	 */
	public $fetch = PDO::FETCH_CLASS;

	/**
	 * 默认数据库连接
	 *
	 * @var string
	 */
	public $defaultConnection = 'mysql';

	/**
	 * 数据库连接配置
	 *
	 * @var array
	 */
	public $connections = [ ];

	/**
	 * 已建立的数据库连接
	 *
	 * @var array
	 */
	private $connectionInstance = [ ];

	/**
	 * 第三方驱动程序
	 *
	 * @var array
	 */
	public static $registrar = [ ];

	/**
	 * 获取一个数据库连接
	 *
	 * 如果未指定数据库名，则返回默认连接。
	 *
	 * <code>
	 * // 获取默认的数据库连接
	 * $connection = DB::connection();
	 *
	 * // Get a specific connection by passing the connection name
	 * $connection = DB::connection('mysql');
	 * </code>
	 *
	 * @param string $connection
	 * @return Database\Connection
	 */
	public function connection($connection = null)
	{
		if (is_null ( $connection )) {
			$connection = $this->defaultConnection;
		}
		if (! isset ( $this->connectionInstance [$connection] )) {
			$config = $this->connections [$connection];
			if (is_null ( $config )) {
				throw new \Exception ( "Database connection is not defined for [$connection]." );
			}
			$this->connectionInstance [$connection] = Kernel::createObject ( '\Leaps\Db\Connection', [ $this->connect ( $config ), $config, $this->fetch, $this->profile ] );
		}
		return $this->connectionInstance [$connection];
	}

	/**
	 * 获得一个给定配置的PDO数据库连接
	 *
	 * @param array $config
	 * @return PDO
	 */
	protected function connect($config)
	{
		return $this->connector ( $config ['driver'] )->connect ( $config );
	}

	/**
	 * 创建一个新的数据库连接实例
	 *
	 * @param string $driver
	 * @return Database\Connectors\Connector
	 */
	protected function connector($driver)
	{
		if (isset ( static::$registrar [$driver] )) {
			$resolver = static::$registrar [$driver] ['connector'];
			return $resolver ();
		}
		switch ($driver) {
			case 'sqlite' :
				return new \Leaps\Db\Connector\SQLite ();

			case 'mysql' :
				return new \Leaps\Db\Connector\MySQL ();

			case 'pgsql' :
				return new \Leaps\Db\Connector\Postgres ();

			case 'sqlsrv' :
				return new \Leaps\Db\Connector\SQLServer ();

			default :
				throw new \Leaps\Db\Exception ( "Database driver [$driver] is not supported." );
		}
	}

	/**
	 * 开始一个表的链式查询
	 *
	 * @param string $table
	 * @param string $connection
	 * @return Database\Query
	 */
	public function table($table, $connection = null)
	{
		return $this->connection ( $connection )->table ( $table );
	}

	/**
	 * Create a new database expression instance.
	 *
	 * Database expressions are used to inject raw SQL into a fluent query.
	 *
	 * @param string $value
	 * @return Expression
	 */
	public function raw($value)
	{
		return new Expression ( $value );
	}

	/**
	 * 字符串安全过滤
	 *
	 * This uses the correct quoting mechanism for the default database connection.
	 *
	 * @param string $value
	 * @return string
	 */
	public function escape($value)
	{
		return $this->connection ()->pdo->quote ( $value );
	}

	/**
	 * 获取所有SQL查询
	 *
	 * @return array
	 */
	public function profile()
	{
		return \Leaps\Db\Connection::$queries;
	}

	/**
	 * 获取最后查询的SQL语句
	 *
	 * @return string
	 */
	public function lastQuery()
	{
		return end ( \Leaps\Db\Connection::$queries );
	}

	/**
	 * 注册数据库连接和语法
	 *
	 * @param string $name
	 * @param Closure $connector
	 * @param Closure $query
	 * @param Closure $schema
	 * @return void
	 */
	public function extend($name, Closure $connector, $query = null, $schema = null)
	{
		if (is_null ( $query )) {
			$query = '\Leaps\Db\Query\Grammar\Grammar';
		}
		static::$registrar [$name] = compact ( 'connector', 'query', 'schema' );
	}

	/**
	 * 默认数据库连接调用的魔术方法
	 *
	 * <code>
	 * // Get the driver name for the default database connection
	 * $driver = DB::driver();
	 *
	 * // Execute a fluent query on the default database connection
	 * $users = DB::table('users')->get();
	 * </code>
	 */
	public function __call($method, $parameters)
	{
		return call_user_func_array ( [ $this->connection (),$method ], $parameters );
	}
}