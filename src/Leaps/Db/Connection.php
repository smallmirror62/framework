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
	 * 该连接的数据库查询语法实例
	 *
	 * @var Query\Grammars\Grammar
	 */
	protected $grammar;

	/**
	 * 所有已经执行的查询(所有连接)
	 *
	 * @var array
	 */
	public static $queries = [];

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
}