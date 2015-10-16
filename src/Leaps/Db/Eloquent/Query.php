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
use Leaps\Db\Eloquent\Relationship\HasManyAndBelongsTo;

class Query
{

	/**
	 * The model instance being queried.
	 *
	 * @var Model
	 */
	public $model;

	/**
	 * The fluent query builder for the query instance.
	 *
	 * @var Query
	 */
	public $table;

	/**
	 * The relationships that should be eagerly loaded by the query.
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
	 * Creat a new query instance for a model.
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
	 * Find a model by its primary key.
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
	 * Get the first model result for the query.
	 *
	 * @param array $columns
	 * @return mixed
	 */
	public function first($columns = ['*'])
	{
		$results = $this->hydrate ( $this->model, $this->table->take ( 1 )->get ( $columns ) );
		return (count ( $results ) > 0) ? head ( $results ) : null;
	}

	/**
	 * Get all of the model results for the query.
	 *
	 * @param array $columns
	 * @return array
	 */
	public function get($columns = array('*'))
	{
		return $this->hydrate ( $this->model, $this->table->get ( $columns ) );
	}

	/**
	 * Get an array of paginated model results.
	 *
	 * @param int $per_page
	 * @param array $columns
	 * @return Paginator
	 */
	public function paginate($per_page = null, $columns = ['*'])
	{
		$per_page = $per_page ?  : $this->model->per_page ();
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
			$new->fill_raw ( $result );
			$models [] = $new;
		}

		if (count ( $results ) > 0) {
			foreach ( $this->model_includes () as $relationship => $constraints ) {
				if (str_contains ( $relationship, '.' )) {
					continue;
				}
				$this->load ( $models, $relationship, $constraints );
			}
		}
		if ($this instanceof Relationship\HasManyAndBelongsTo) {
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
		$query->model->includes = $this->nestedInclude ( $relationship );
		$query->table->resetWhere ();
		$query->eagerly_constrain ( $results );
		if (! is_null ( $constraints )) {
			$query->table->whereNested ( $constraints );
		}
		$query->initialize ( $results, $relationship );
		$query->match ( $relationship, $results, $query->get () );
	}

	/**
	 * Gather the nested includes for a given relationship.
	 *
	 * @param string $relationship
	 * @return array
	 */
	protected function nestedInclude($relationship)
	{
		$nested = array ();
		foreach ( $this->modelInclude () as $include => $constraints ) {
			if (starts_with ( $include, $relationship . '.' )) {
				$nested [substr ( $include, strlen ( $relationship . '.' ) )] = $constraints;
			}
		}

		return $nested;
	}

	/**
	 * Get the eagerly loaded relationships for the model.
	 *
	 * @return array
	 */
	protected function modelInclude()
	{
		$includes = [ ];
		foreach ( $this->model->includes as $relationship => $constraints ) {
			if (is_numeric ( $relationship )) {
				list ( $relationship, $constraints ) = [ $constraints,null ];
			}

			$includes [$relationship] = $constraints;
		}

		return $includes;
	}

	/**
	 * Get a fluent query builder for the model.
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
	 * Handle dynamic method calls to the query.
	 *
	 * @param string $method
	 * @param array $parameters
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