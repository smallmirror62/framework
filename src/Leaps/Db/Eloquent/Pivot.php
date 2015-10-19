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
namespace Leaps\Db\Eloquent;

class Pivot extends Model
{

	/**
	 * 透视表的表名称
	 *
	 * @var string
	 */
	protected $pivotTable;

	/**
	 * 用于此模型的数据库连接
	 *
	 * @var \Leaps\Db\Connection
	 */
	protected $pivotConnection;

	/**
	 * 自动设置模型更新和创建时间
	 *
	 * @var bool
	 */
	public static $timestamps = true;

	/**
	 * 创建一个新的透视表实例
	 *
	 * @param string $table 表名
	 * @param string $connection 连接
	 * @return void
	 */
	public function __construct($table, $connection = null)
	{
		$this->pivotTable = $table;
		$this->pivotConnection = $connection;
		parent::__construct ( [ ], true );
	}

	/**
	 * 获取透视表表名
	 *
	 * @return string
	 */
	public function table()
	{
		return $this->pivotTable;
	}

	/**
	 * 获取透视表数据库连接
	 *
	 * @return string
	 */
	public function connection()
	{
		return $this->pivotConnection;
	}
}