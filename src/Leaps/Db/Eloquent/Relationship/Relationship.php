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
namespace Leaps\Db\Eloquent\Relationship;

use Leaps\Db\Eloquent\Model;
use Leaps\Db\Eloquent\Query;

abstract class Relationship extends Query
{

	/**
	 * The base model for the relationship.
	 *
	 * @var Model
	 */
	protected $base;

	/**
	 * Create a new has one or many association instance.
	 *
	 * @param Model $model
	 * @param string $associated
	 * @param string $foreign
	 * @return void
	 */
	public function __construct($model, $associated, $foreign)
	{
		$this->foreign = $foreign;

		if ($associated instanceof Model) {
			$this->model = $associated;
		} else {
			$this->model = new $associated ();
		}

		if ($model instanceof Model) {
			$this->base = $model;
		} else {
			$this->base = new $model ();
		}

		$this->table = $this->table ();

		$this->constrain ();
	}

	/**
	 * Get the foreign key name for the given model.
	 *
	 * @param string $model
	 * @param string $foreign
	 * @return string
	 */
	public static function foreign($model, $foreign = null)
	{
		if (! is_null ( $foreign ))
			return $foreign;
		if (is_object ( $model )) {
			$model = class_basename ( $model );
		}

		return strtolower ( basename ( $model ) . '_id' );
	}

	/**
	 * Get a freshly instantiated instance of the related model class.
	 *
	 * @param array $attributes
	 * @return Model
	 */
	protected function fresh_model($attributes = array())
	{
		$class = get_class ( $this->model );

		return new $class ( $attributes );
	}

	/**
	 * Get the foreign key for the relationship.
	 *
	 * @return string
	 */
	public function foreign_key()
	{
		return static::foreign ( $this->base, $this->foreign );
	}

	/**
	 * Gather all the primary keys from a result set.
	 *
	 * @param array $results
	 * @return array
	 */
	public function keys($results)
	{
		$keys = array ();

		foreach ( $results as $result ) {
			$keys [] = $result->get_key ();
		}

		return array_unique ( $keys );
	}

	/**
	 * The relationships that should be eagerly loaded by the query.
	 *
	 * @param array $includes
	 * @return Relationship
	 */
	public function with($includes)
	{
		$this->model->includes = ( array ) $includes;

		return $this;
	}
}