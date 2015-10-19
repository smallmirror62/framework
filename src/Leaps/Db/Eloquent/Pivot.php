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
	 * Create a new pivot table instance.
	 *
	 * @param string $table
	 * @param string $connection
	 * @return void
	 */
	public function __construct($table, $connection = null)
	{
		$this->pivotTable = $table;
		$this->pivotConnection = $connection;
		parent::__construct ( [ ], true );
	}

	/**
	 * Get the name of the pivot table.
	 *
	 * @return string
	 */
	public function table()
	{
		return $this->pivotTable;
	}

	/**
	 * Get the connection used by the pivot table.
	 *
	 * @return string
	 */
	public function connection()
	{
		return $this->pivotConnection;
	}
}