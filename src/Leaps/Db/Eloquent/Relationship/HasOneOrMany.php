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

class HasOneOrMany extends Relationship
{

	/**
	 * Insert a new record for the association.
	 *
	 * If save is successful, the model will be returned, otherwise false.
	 *
	 * @param Model|array $attributes
	 * @return Model|false
	 */
	public function insert($attributes)
	{
		if ($attributes instanceof Model) {
			$attributes->setAttribute ( $this->foreignKey (), $this->base->getKey () );
			return $attributes->save () ? $attributes : false;
		} else {
			$attributes [$this->foreignKey ()] = $this->base->getKey ();
			return $this->model->create ( $attributes );
		}
	}

	/**
	 * Update a record for the association.
	 *
	 * @param array $attributes
	 * @return bool
	 */
	public function update(array $attributes)
	{
		if ($this->model->timestamps ()) {
			$attributes ['updated_at'] = new \DateTime ();
		}

		return $this->table->update ( $attributes );
	}

	/**
	 * Set the proper constraints on the relationship table.
	 *
	 * @return void
	 */
	protected function constrain()
	{
		$this->table->where ( $this->foreignKey (), '=', $this->base->getKey () );
	}

	/**
	 * Set the proper constraints on the relationship table for an eager load.
	 *
	 * @param array $results
	 * @return void
	 */
	public function eagerlyConstrain($results)
	{
		$this->table->whereIn ( $this->foreignKey (), $this->keys ( $results ) );
	}
}