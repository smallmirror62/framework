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

class Connection
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
	 * The query grammar instance for the connection.
	 *
	 * @var Query\Grammars\Grammar
	 */
	protected $grammar;

	/**
	 * All of the queries that have been executed on all connections.
	 *
	 * @var array
	 */
	public static $queries = [];

	/**
	 * Create a new database connection instance.
	 *
	 * @param PDO $pdo
	 * @param array $config
	 * @return void
	 */
	public function __construct(\PDO $pdo, $config)
	{
		$this->pdo = $pdo;
		$this->config = $config;
	}
}