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

class HasOne extends HasOneOrMany
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
			$dictionary [$child->$foreign] = $child;
		}
		foreach ( $parents as $parent ) {
			if (array_key_exists ( $key = $parent->getkey (), $dictionary )) {
				$parent->relationships [$relationship] = $dictionary [$key];
			}
		}
	}
}