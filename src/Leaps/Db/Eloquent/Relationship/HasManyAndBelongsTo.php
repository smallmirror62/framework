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

use Leaps;
use Leaps\Db\Eloquent\Model;
use Leaps\Db\Eloquent\Pivot;
use Leaps\Helper\StringHelper;

class HasManyAndBelongsTo extends Relationship
{

	/**
	 * The name of the intermediate, joining table.
	 *
	 * @var string
	 */
	protected $joining;

	/**
	 * The other or "associated" key.
	 * This is the foreign key of the related model.
	 *
	 * @var string
	 */
	protected $other;

	/**
	 * The columns on the joining table that should be fetched.
	 *
	 * @var array
	 */
	protected $with = [ 'id' ];

	/**
	 * Create a new many to many relationship instance.
	 *
	 * @param Model $model
	 * @param string $associated
	 * @param string $table
	 * @param string $foreign
	 * @param string $other
	 * @return void
	 */
	public function __construct($model, $associated, $table, $foreign, $other)
	{
		$this->other = $other;
		$this->joining = $table ?  : $this->joining ( $model, $associated );
		if (Pivot::$timestamps) {
			$this->with [] = 'created_at';
			$this->with [] = 'updated_at';
		}
		parent::__construct ( $model, $associated, $foreign );
	}

	/**
	 * Determine the joining table name for the relationship.
	 *
	 * By default, the name is the models sorted and joined with underscores.
	 *
	 * @return string
	 */
	protected function joining($model, $associated)
	{
		$models = [ Leaps::classBasename ( $model ),Leaps::classBasename ( $associated ) ];
		sort ( $models );
		return strtolower ( $models [0] . '_' . $models [1] );
	}

	/**
	 * Get the properly hydrated results for the relationship.
	 *
	 * @return array
	 */
	public function results()
	{
		return parent::get ();
	}

	/**
	 * Insert a new record into the joining table of the association.
	 *
	 * @param Model|int $id
	 * @param array $attributes
	 * @return bool
	 */
	public function attach($id, $attributes = [])
	{
		if ($id instanceof Model)
			$id = $id->getKey ();
		$joining = array_merge ( $this->joinRecord ( $id ), $attributes );
		return $this->insertJoining ( $joining );
	}

	/**
	 * Detach a record from the joining table of the association.
	 *
	 * @param array|Model|int $ids
	 * @return bool
	 */
	public function detach($ids)
	{
		if ($ids instanceof Model)
			$ids = [ $ids->getKey () ];
		elseif (! is_array ( $ids ))
			$ids = [ $ids ];
		return $this->pivot ()->whereIn ( $this->otherKey (), $ids )->delete ();
	}

	/**
	 * Sync the joining table with the array of given IDs.
	 *
	 * @param array $ids
	 * @return bool
	 */
	public function sync($ids)
	{
		$current = $this->pivot ()->lists ( $this->otherKey () );
		$ids = ( array ) $ids;
		foreach ( $ids as $id ) {
			if (! in_array ( $id, $current )) {
				$this->attach ( $id );
			}
		}
		$detach = array_diff ( $current, $ids );
		if (count ( $detach ) > 0) {
			$this->detach ( $detach );
		}
	}

	/**
	 * Insert a new record for the association.
	 *
	 * @param Model|array $attributes
	 * @param array $joining
	 * @return bool
	 */
	public function insert($attributes, $joining = [])
	{
		if ($attributes instanceof Model) {
			$attributes = $attributes->attributes;
		}
		$model = $this->model->create ( $attributes );
		if ($model instanceof Model) {
			$joining = array_merge ( $this->joinRecord ( $model->getKey () ), $joining );
			$result = $this->insertJoining ( $joining );
		}
		return $model instanceof Model and $result;
	}

	/**
	 * Delete all of the records from the joining table for the model.
	 *
	 * @return int
	 */
	public function delete()
	{
		return $this->pivot ()->delete ();
	}

	/**
	 * Create an array representing a new joining record for the association.
	 *
	 * @param int $id
	 * @return array
	 */
	protected function joinRecord($id)
	{
		return array ($this->foreignKey () => $this->base->getKey (),$this->otherKey () => $id );
	}

	/**
	 * Insert a new record into the joining table of the association.
	 *
	 * @param array $attributes
	 * @return void
	 */
	protected function insertJoining($attributes)
	{
		if (Pivot::$timestamps) {
			$attributes ['created_at'] = new \DateTime ();
			$attributes ['updated_at'] = $attributes ['created_at'];
		}
		return $this->joiningTable ()->insert ( $attributes );
	}

	/**
	 * Get a fluent query for the joining table of the relationship.
	 *
	 * @return Query
	 */
	protected function joiningTable()
	{
		return $this->connection ()->table ( $this->joining );
	}

	/**
	 * Set the proper constraints on the relationship table.
	 *
	 * @return void
	 */
	protected function constrain()
	{
		$other = $this->otherKey ();
		$foreign = $this->foreignKey ();
		$this->setSelect ( $foreign, $other )->setJoin ( $other )->setWhere ( $foreign );
	}

	/**
	 * Set the SELECT clause on the query builder for the relationship.
	 *
	 * @param string $foreign
	 * @param string $other
	 * @return void
	 */
	protected function setSelect($foreign, $other)
	{
		$columns = array ($this->model->table () . '.*' );
		$this->with = array_merge ( $this->with, [ $foreign,$other ] );
		foreach ( $this->with as $column ) {
			$columns [] = $this->joining . '.' . $column . ' as pivot_' . $column;
		}
		$this->table->select ( $columns );
		return $this;
	}

	/**
	 * Set the JOIN clause on the query builder for the relationship.
	 *
	 * @param string $other
	 * @return void
	 */
	protected function setJoin($other)
	{
		$this->table->join ( $this->joining, $this->associatedKey (), '=', $this->joining . '.' . $other );
		return $this;
	}

	/**
	 * Set the WHERE clause on the query builder for the relationship.
	 *
	 * @param string $foreign
	 * @return void
	 */
	protected function setWhere($foreign)
	{
		$this->table->where ( $this->joining . '.' . $foreign, '=', $this->base->getKey () );
		return $this;
	}

	/**
	 * Initialize a relationship on an array of parent models.
	 *
	 * @param array $parents
	 * @param string $relationship
	 * @return void
	 */
	public function initialize(&$parents, $relationship)
	{
		foreach ( $parents as &$parent ) {
			$parent->relationships [$relationship] = [ ];
		}
	}

	/**
	 * Set the proper constraints on the relationship table for an eager load.
	 *
	 * @param array $results
	 * @return void
	 */
	public function eagerlyConstrain($results)
	{
		$this->table->whereIn ( $this->joining . '.' . $this->foreignKey (), $this->keys ( $results ) );
	}

	/**
	 * Match eagerly loaded child models to their parent models.
	 *
	 * @param array $parents
	 * @param array $children
	 * @return void
	 */
	public function match($relationship, &$parents, $children)
	{
		$foreign = $this->foreignKey ();
		$dictionary = [ ];
		foreach ( $children as $child ) {
			$dictionary [$child->pivot->$foreign] [] = $child;
		}
		foreach ( $parents as $parent ) {
			if (array_key_exists ( $key = $parent->getKey (), $dictionary )) {
				$parent->relationships [$relationship] = $dictionary [$key];
			}
		}
	}

	/**
	 * Hydrate the Pivot model on an array of results.
	 *
	 * @param array $results
	 * @return void
	 */
	protected function hydratePivot(&$results)
	{
		foreach ( $results as &$result ) {
			$pivot = new Pivot ( $this->joining, $this->model->connection () );
			foreach ( $result->attributes as $key => $value ) {
				if (StringHelper::startsWith( $key, 'pivot' )) {
					$pivot->{substr ( $key, 5 )} = $value;
					$result->purge ( $key );
				}
			}
			$result->relationships ['pivot'] = $pivot;
			$pivot->sync () and $result->sync ();
		}
	}

	/**
	 * Set the columns on the joining table that should be fetched.
	 *
	 * @param array $column
	 * @return Relationship
	 */
	public function with($columns)
	{
		$columns = (is_array ( $columns )) ? $columns : func_get_args ();
		$this->with = array_unique ( array_merge ( $this->with, $columns ) );
		$this->setSelect ( $this->foreignKey (), $this->otherKey () );
		return $this;
	}

	/**
	 * Get a relationship instance of the pivot table.
	 *
	 * @return Has_Many
	 */
	public function pivot()
	{
		$pivot = new Pivot ( $this->joining, $this->model->connection () );
		return new HasMany ( $this->base, $pivot, $this->foreignKey () );
	}

	/**
	 * Get the other or associated key for the relationship.
	 *
	 * @return string
	 */
	protected function otherKey()
	{
		return Relationship::foreign ( $this->model, $this->other );
	}

	/**
	 * Get the fully qualified associated table's primary key.
	 *
	 * @return string
	 */
	protected function associatedKey()
	{
		return $this->model->table () . '.' . $this->model->key ();
	}
}