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

use Leaps\Db;
use Leaps\Helper\StringHelper;
use Leaps\Db\Eloquent\Relationship\HasManyAndBelongsTo;

class Query
{

	/**
	 * 被查询的模型实例
	 *
	 * @var \Leaps\Db\Model
	 */
	public $model;

	/**
	 * 表查询实例
	 *
	 * @var Query
	 */
	public $table;

	/**
	 * 查询的关系
	 *
	 * @var array
	 */
	public $includes = [ ];

	/**
	 * The methods that should be returned from the fluent query builder.
	 *
	 * @var array
	 */
	public $passthru = [ 'lists','only','insert','insert_get_id','update','increment','delete','decrement','count','min','max','avg','sum' ];

	/**
	 * 从模型创建查询
	 *
	 * @param Model $model
	 * @return void
	 */
	public function __construct($model)
	{
		$this->model = ($model instanceof Model) ? $model : new $model ();
		$this->table = $this->table ();
	}

	/**
	 * 查找主键
	 *
	 * @param mixed $id
	 * @param array $columns
	 * @return mixed
	 */
	public function find($id, $columns = ['*'])
	{
		$model = $this->model;
		$this->table->where ( $model::$key, '=', $id );
		return $this->first ( $columns );
	}

	/**
	 * 获取查询的第一个模型结果
	 *
	 * @param array $columns 字段
	 * @return mixed
	 */
	public function first($columns = ['*'])
	{
		$results = $this->hydrate ( $this->model, $this->table->take ( 1 )->get ( $columns ) );
		return (count ( $results ) > 0) ? reset ( $results ) : null;
	}

	/**
	 * 获得查询的所有模型结果
	 *
	 * @param array $columns
	 * @return array
	 */
	public function get($columns = array('*'))
	{
		return $this->hydrate ( $this->model, $this->table->get ( $columns ) );
	}

	/**
	 * 获取分页模型结果数组
	 *
	 * @param int $per_page
	 * @param array $columns
	 * @return Paginator
	 */
	public function paginate($per_page = null, $columns = ['*'])
	{
		$per_page = $per_page ?  : $this->model->perPage ();
		$paginator = $this->table->paginate ( $per_page, $columns );
		$paginator->results = $this->hydrate ( $this->model, $paginator->results );
		return $paginator;
	}

	/**
	 * Hydrate an array of models from the given results.
	 *
	 * @param Model $model
	 * @param array $results
	 * @return array
	 */
	public function hydrate($model, $results)
	{
		$class = get_class ( $model );
		$models = [ ];
		foreach ( ( array ) $results as $result ) {
			$result = ( array ) $result;
			$new = new $class ( [ ], true );
			$new->fillRaw ( $result );
			$models [] = $new;
		}
		if (count ( $results ) > 0) {
			foreach ( $this->modelInclude () as $relationship => $constraints ) {
				if (StringHelper::contain ( $relationship, '.' )) {
					continue;
				}
				$this->load ( $models, $relationship, $constraints );
			}
		}
		if ($this instanceof HasManyAndBelongsTo) {
			$this->hydratePivot ( $models );
		}

		return $models;
	}

	/**
	 * Hydrate an eagerly loaded relationship on the model results.
	 *
	 * @param array $results
	 * @param string $relationship
	 * @param array|null $constraints
	 * @return void
	 */
	protected function load(&$results, $relationship, $constraints)
	{
		$query = $this->model->$relationship ();
		$query->model->include = $this->nestedInclude ( $relationship );
		$query->table->resetWhere ();
		$query->eagerlyConstrain ( $results );
		if (! is_null ( $constraints )) {
			$query->table->whereNested ( $constraints );
		}
		$query->initialize ( $results, $relationship );
		$query->match ( $relationship, $results, $query->get () );
	}

	/**
	 * 集合嵌套包含一个给定的关系
	 *
	 * @param string $relationship
	 * @return array
	 */
	protected function nestedInclude($relationship)
	{
		$nested = array ();
		foreach ( $this->modelInclude () as $include => $constraints ) {
			if (StringHelper::contain ( $include, $relationship . '.' )) {
				$nested [substr ( $include, strlen ( $relationship . '.' ) )] = $constraints;
			}
		}
		return $nested;
	}

	/**
	 * 得到模型关系
	 *
	 * @return array
	 */
	protected function modelInclude()
	{
		$includes = [ ];
		foreach ( $this->model->include as $relationship => $constraints ) {
			if (is_numeric ( $relationship )) {
				list ( $relationship, $constraints ) = [ $constraints,null ];
			}
			$includes [$relationship] = $constraints;
		}
		return $includes;
	}

	/**
	 * 获取模型的一个流利的查询生成器
	 *
	 * @return Query
	 */
	protected function table()
	{
		return $this->connection ()->table ( $this->model->table () );
	}

	/**
	 * 获取模型数据库连接
	 *
	 * @return Connection
	 */
	public function connection()
	{
		return Database::connection ( $this->model->connection () );
	}

	/**
	 * 动态调用查询魔术方法
	 *
	 * @param string $method 方法名称
	 * @param array $parameters 参数
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
		$result = call_user_func_array ( [ $this->table,$method ], $parameters );
		if (in_array ( $method, $this->passthru )) {
			return $result;
		}
		return $this;
	}
}