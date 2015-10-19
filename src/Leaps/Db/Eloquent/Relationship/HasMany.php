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

class HasMany extends HasOneOrMany
{

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
	 * Sync the association table with an array of models.
	 *
	 * @param mixed $models
	 * @return bool
	 */
	public function save($models)
	{
		if (! is_array ( $models ))
			$models = array ($models );

		$current = $this->table->lists ( $this->model->key () );
		foreach ( $models as $attributes ) {
			$class = get_class ( $this->model );
			if ($attributes instanceof $class) {
				$model = $attributes;
			} else {
				$model = $this->freshModel ( $attributes );
			}
			$foreign = $this->foreignKey ();
			$model->$foreign = $this->base->getKey ();
			$id = $model->getKey ();
			$model->exists = (! is_null ( $id ) and in_array ( $id, $current ));
			$model->original = [ ];
			$model->save ();
		}

		return true;
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
			$dictionary [$child->$foreign] [] = $child;
		}
		foreach ( $parents as $parent ) {
			if (array_key_exists ( $key = $parent->getKey (), $dictionary )) {
				$parent->relationships [$relationship] = $dictionary [$key];
			}
		}
	}
}