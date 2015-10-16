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
namespace Leaps\Db\Connector;

use PDO;

abstract class Connector
{

	/**
	 * PDO连接参数
	 *
	 * @var array
	 */
	protected $options = [
			PDO::ATTR_CASE => PDO::CASE_LOWER,
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
			PDO::ATTR_STRINGIFY_FETCHES => false,
			PDO::ATTR_EMULATE_PREPARES => false
	];

	/**
	 * 建立一个PDO的数据库连接
	 *
	 * @param array $config
	 * @return PDO
	 */
	abstract public function connect($config);

	/**
	 * 获取配置PDO连接选项
	 *
	 * 开发者指定的选项将重写默认的连接选项
	 *
	 * @param array $config
	 * @return array
	 */
	protected function options($config)
	{
		$options = (isset ( $config ['options'] )) ? $config ['options'] : [ ];
		return $options + $this->options;
	}
}