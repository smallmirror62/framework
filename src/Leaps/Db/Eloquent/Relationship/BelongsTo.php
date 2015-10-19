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

class BelongsTo extends Relationship
{

	/**
	 * Get the properly hydrated results for the relationship.
	 *
	 * @return Model
	 */
	public function results()
	{
		return parent::first ();
	}

	/**
	 * Update the parent model of the relationship.
	 *
	 * @param Model|array $attributes
	 * @return int
	 */
	public function update($attributes)
	{
		$attributes = ($attributes instanceof Model) ? $attributes->getDirty () : $attributes;
		return $this->model->update ( $this->foreignValue (), $attributes );
	}

	/**
	 * Set the proper constraints on the relationship table.
	 *
	 * @return void
	 */
	protected function constrain()
	{
		$this->table->where ( $this->model->key (), '=', $this->foreignValue () );
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
			$parent->relationships [$relationship] = null;
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
		$keys = [ ];
		foreach ( $results as $result ) {
			if (! is_null ( $key = $result->{$this->foreignKey ()} )) {
				$keys [] = $key;
			}
		}
		if (count ( $keys ) == 0)
			$keys = [ 0 ];
		$this->table->whereIn ( $this->model->key (), array_unique ( $keys ) );
	}

	/**
	 * Match eagerly loaded child models to their parent models.
	 *
	 * @param array $children
	 * @param array $parents
	 * @return void
	 */
	public function match($relationship, &$children, $parents)
	{
		$foreign = $this->foreignKey ();
		$dictionary = [ ];
		foreach ( $parents as $parent ) {
			$dictionary [$parent->getKey ()] = $parent;
		}
		foreach ( $children as $child ) {
			if (array_key_exists ( $child->$foreign, $dictionary )) {
				$child->relationships [$relationship] = $dictionary [$child->$foreign];
			}
		}
	}

	/**
	 * Get the value of the foreign key from the base model.
	 *
	 * @return mixed
	 */
	public function foreignValue()
	{
		return $this->base->getAttribute ( $this->foreign );
	}

	/**
	 * Bind an object over a belongs-to relation using its id.
	 *
	 * @return Eloquent
	 */
	public function bind($id)
	{
		$this->base->fill ( array ($this->foreign => $id ) )->save ();
		return $this->base;
	}
}