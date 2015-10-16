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

use Laravel\Str;
use Laravel\Event;
use Leaps\Database;
use Leaps\Database\Eloquent\Relationships\Has_Many_And_Belongs_To;

abstract class Model
{

	/**
	 * All of the model's attributes.
	 *
	 * @var array
	 */
	public $attributes = [ ];

	/**
	 * The model's attributes in their original state.
	 *
	 * @var array
	 */
	public $original = [ ];

	/**
	 * The relationships that have been loaded for the query.
	 *
	 * @var array
	 */
	public $relationships = [ ];

	/**
	 * Indicates if the model exists in the database.
	 *
	 * @var bool
	 */
	public $exists = false;

	/**
	 * The relationships that should be eagerly loaded.
	 *
	 * @var array
	 */
	public $includes = array ();

	/**
	 * The primary key for the model on the database table.
	 *
	 * @var string
	 */
	public static $key = 'id';

	/**
	 * The attributes that are accessible for mass assignment.
	 *
	 * @var array
	 */
	public static $accessible;

	/**
	 * The attributes that should be excluded from to_array.
	 *
	 * @var array
	 */
	public static $hidden = [ ];

	/**
	 * Indicates if the model has update and creation timestamps.
	 *
	 * @var bool
	 */
	public static $timestamps = true;

	/**
	 * The name of the table associated with the model.
	 *
	 * @var string
	 */
	public static $table;

	/**
	 * The name of the database connection that should be used for the model.
	 *
	 * @var string
	 */
	public static $connection;

	/**
	 * The name of the sequence associated with the model.
	 *
	 * @var string
	 */
	public static $sequence;

	/**
	 * The default number of models to show per page when paginating.
	 *
	 * @var int
	 */
	public static $perPage = 20;

	/**
	 * Create a new Eloquent model instance.
	 *
	 * @param array $attributes
	 * @param bool $exists
	 * @return void
	 */
	public function __construct($attributes = [], $exists = false)
	{
		$this->exists = $exists;
		$this->fill ( $attributes );
	}

	/**
	 * Hydrate the model with an array of attributes.
	 *
	 * @param array $attributes
	 * @param bool $raw
	 * @return Model
	 */
	public function fill(array $attributes, $raw = false)
	{
		foreach ( $attributes as $key => $value ) {
			if ($raw) {
				$this->setAttribute ( $key, $value );
				continue;
			}
			if (is_array ( static::$accessible )) {
				if (in_array ( $key, static::$accessible )) {
					$this->$key = $value;
				}
			} else {
				$this->$key = $value;
			}
		}
		if (count ( $this->original ) === 0) {
			$this->original = $this->attributes;
		}
		return $this;
	}

	/**
	 * Fill the model with the contents of the array.
	 *
	 * No mutators or accessibility checks will be accounted for.
	 *
	 * @param array $attributes
	 * @return Model
	 */
	public function fillRaw(array $attributes)
	{
		return $this->fill ( $attributes, true );
	}

	/**
	 * Set the accessible attributes for the given model.
	 *
	 * @param array $attributes
	 * @return void
	 */
	public static function accessible($attributes = null)
	{
		if (is_null ( $attributes ))
			return static::$accessible;
		static::$accessible = $attributes;
	}

	/**
	 * Create a new model and store it in the database.
	 *
	 * If save is successful, the model will be returned, otherwise false.
	 *
	 * @param array $attributes
	 * @return Model|false
	 */
	public static function create($attributes)
	{
		$model = new static ( $attributes );
		$success = $model->save ();
		return ($success) ? $model : false;
	}

	/**
	 * Update a model instance in the database.
	 *
	 * @param mixed $id
	 * @param array $attributes
	 * @return int
	 */
	public static function update($id, $attributes)
	{
		$model = new static ( array (), true );
		$model->fill ( $attributes );
		if (static::$timestamps)
			$model->timestamp ();
		return $model->query ()->where ( $model->key (), '=', $id )->update ( $model->attributes );
	}

	/**
	 * Get all of the models in the database.
	 *
	 * @return array
	 */
	public static function all()
	{
		return with ( new static () )->query ()->get ();
	}

	/**
	 * The relationships that should be eagerly loaded by the query.
	 *
	 * @param array $includes
	 * @return Model
	 */
	public function _with($includes)
	{
		$this->includes = ( array ) $includes;
		return $this;
	}

	/**
	 * Get the query for a one-to-one association.
	 *
	 * @param string $model
	 * @param string $foreign
	 * @return Relationship
	 */
	public function hasOne($model, $foreign = null)
	{
		return $this->hasOneOrMany ( __FUNCTION__, $model, $foreign );
	}

	/**
	 * Get the query for a one-to-many association.
	 *
	 * @param string $model
	 * @param string $foreign
	 * @return Relationship
	 */
	public function hasMany($model, $foreign = null)
	{
		return $this->hasOneOrMany ( __FUNCTION__, $model, $foreign );
	}

	/**
	 * Get the query for a one-to-one / many association.
	 *
	 * @param string $type
	 * @param string $model
	 * @param string $foreign
	 * @return Relationship
	 */
	protected function hasOneOrMany($type, $model, $foreign)
	{
		if ($type == 'has_one') {
			return new Relationship\HasOne ( $this, $model, $foreign );
		} else {
			return new Relationship\HasMany ( $this, $model, $foreign );
		}
	}

	/**
	 * Get the query for a one-to-one (inverse) relationship.
	 *
	 * @param string $model
	 * @param string $foreign
	 * @return Relationship
	 */
	public function belongsTo($model, $foreign = null)
	{
		if (is_null ( $foreign )) {
			list ( , $caller ) = debug_backtrace ( false );

			$foreign = "{$caller['function']}_id";
		}

		return new Relationship\BelongsTo ( $this, $model, $foreign );
	}

	/**
	 * Get the query for a many-to-many relationship.
	 *
	 * @param string $model
	 * @param string $table
	 * @param string $foreign
	 * @param string $other
	 * @return Has_Many_And_Belongs_To
	 */
	public function HasManyAndBelongsTo($model, $table = null, $foreign = null, $other = null)
	{
		return new HasManyAndBelongsTo ( $this, $model, $table, $foreign, $other );
	}

	/**
	 * Save the model and all of its relations to the database.
	 *
	 * @return bool
	 */
	public function push()
	{
		$this->save ();
		foreach ( $this->relationships as $name => $models ) {
			if (! is_array ( $models )) {
				$models = array ($models );
			}
			foreach ( $models as $model ) {
				$model->push ();
			}
		}
	}

	/**
	 * Save the model instance to the database.
	 *
	 * @return bool
	 */
	public function save()
	{
		if (! $this->dirty ())
			return true;
		if (static::$timestamps) {
			$this->timestamp ();
		}
		$this->fire_event ( 'saving' );
		if ($this->exists) {
			$query = $this->query ()->where ( static::$key, '=', $this->getKey () );
			$result = $query->update ( $this->getDirty () ) === 1;
			if ($result)
				$this->fireEvent ( 'updated' );
		} else {
			$id = $this->query ()->insertGetID ( $this->attributes, $this->key () );
			$this->set_key ( $id );
			$this->exists = $result = is_numeric ( $this->getKey () );
			if ($result)
				$this->fireEvent ( 'created' );
		}
		$this->original = $this->attributes;
		if ($result) {
			$this->fireEvent ( 'saved' );
		}
		return $result;
	}

	/**
	 * Delete the model from the database.
	 *
	 * @return int
	 */
	public function delete()
	{
		if ($this->exists) {
			$this->fire_event ( 'deleting' );
			$result = $this->query ()->where ( static::$key, '=', $this->getKey () )->delete ();
			$this->fire_event ( 'deleted' );
			return $result;
		}
	}

	/**
	 * Set the update and creation timestamps on the model.
	 *
	 * @return void
	 */
	public function timestamp()
	{
		$this->updated_at = new \DateTime ();
		if (! $this->exists)
			$this->created_at = $this->updated_at;
	}

	/**
	 * Updates the timestamp on the model and immediately saves it.
	 *
	 * @return void
	 */
	public function touch()
	{
		$this->timestamp ();
		$this->save ();
	}

	/**
	 * Get a new fluent query builder instance for the model.
	 *
	 * @return Query
	 */
	protected function _query()
	{
		return new Query ( $this );
	}

	/**
	 * Sync the original attributes with the current attributes.
	 *
	 * @return bool
	 */
	final public function sync()
	{
		$this->original = $this->attributes;
		return true;
	}

	/**
	 * Determine if a given attribute has changed from its original state.
	 *
	 * @param string $attribute
	 * @return bool
	 */
	public function changed($attribute)
	{
		return array_get ( $this->attributes, $attribute ) != array_get ( $this->original, $attribute );
	}

	/**
	 * Determine if the model has been changed from its original state.
	 *
	 * Models that haven't been persisted to storage are always considered dirty.
	 *
	 * @return bool
	 */
	public function dirty()
	{
		return ! $this->exists or count ( $this->getDirty () ) > 0;
	}

	/**
	 * Get the name of the table associated with the model.
	 *
	 * @return string
	 */
	public function table()
	{
		return static::$table ?  : strtolower ( Str::plural ( class_basename ( $this ) ) );
	}

	/**
	 * Get the dirty attributes for the model.
	 *
	 * @return array
	 */
	public function getDirty()
	{
		$dirty = array ();
		foreach ( $this->attributes as $key => $value ) {
			if (! array_key_exists ( $key, $this->original ) or $value != $this->original [$key]) {
				$dirty [$key] = $value;
			}
		}
		return $dirty;
	}

	/**
	 * Get the value of the primary key for the model.
	 *
	 * @return int
	 */
	public function getKey()
	{
		return array_get ( $this->attributes, static::$key );
	}

	/**
	 * Set the value of the primary key for the model.
	 *
	 * @param int $value
	 * @return void
	 */
	public function setKey($value)
	{
		return $this->setAttribute ( static::$key, $value );
	}

	/**
	 * Get a given attribute from the model.
	 *
	 * @param string $key
	 */
	public function getAttribute($key)
	{
		return array_get ( $this->attributes, $key );
	}

	/**
	 * Set an attribute's value on the model.
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function setAttribute($key, $value)
	{
		$this->attributes [$key] = $value;
	}

	/**
	 * Remove an attribute from the model.
	 *
	 * @param string $key
	 */
	final public function purge($key)
	{
		unset ( $this->original [$key] );
		unset ( $this->attributes [$key] );
	}

	/**
	 * Get the model attributes and relationships in array form.
	 *
	 * @return array
	 */
	public function toArray()
	{
		$attributes = array ();
		foreach ( array_keys ( $this->attributes ) as $attribute ) {
			if (! in_array ( $attribute, static::$hidden )) {
				$attributes [$attribute] = $this->$attribute;
			}
		}

		foreach ( $this->relationships as $name => $models ) {
			if (in_array ( $name, static::$hidden ))
				continue;
			if ($models instanceof Model) {
				$attributes [$name] = $models->toArray ();
			} elseif (is_array ( $models )) {
				$attributes [$name] = [ ];
				foreach ( $models as $id => $model ) {
					$attributes [$name] [$id] = $model->toArray ();
				}
			} elseif (is_null ( $models )) {
				$attributes [$name] = $models;
			}
		}

		return $attributes;
	}

	/**
	 * Fire a given event for the model.
	 *
	 * @param string $event
	 * @return array
	 */
	protected function fireEvent($event)
	{
		$events = [ "model.{$event}","model.{$event}: " . get_class ( $this ) ];
		Event::fire ( $events, [ $this ] );
	}

	/**
	 * Handle the dynamic retrieval of attributes and associations.
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __get($key)
	{
		if (array_key_exists ( $key, $this->relationships )) {
			return $this->relationships [$key];
		} elseif (array_key_exists ( $key, $this->attributes )) {
			return $this->{"get_{$key}"} ();
		} elseif (method_exists ( $this, $key )) {
			return $this->relationships [$key] = $this->$key ()->results ();
		} else {
			return $this->{"get_{$key}"} ();
		}
	}

	/**
	 * Handle the dynamic setting of attributes.
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function __set($key, $value)
	{
		$this->{"set_{$key}"} ( $value );
	}

	/**
	 * Determine if an attribute exists on the model.
	 *
	 * @param string $key
	 * @return bool
	 */
	public function __isset($key)
	{
		foreach ( [ 'attributes','relationships' ] as $source ) {
			if (array_key_exists ( $key, $this->{$source} ))
				return ! empty ( $this->{$source} [$key] );
		}
		return false;
	}

	/**
	 * Remove an attribute from the model.
	 *
	 * @param string $key
	 * @return void
	 */
	public function __unset($key)
	{
		foreach ( array ('attributes','relationships' ) as $source ) {
			unset ( $this->{$source} [$key] );
		}
	}

	/**
	 * Handle dynamic method calls on the model.
	 *
	 * @param string $method
	 * @param array $parameters
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
		$meta = array ('key','table','connection','sequence','per_page','timestamps' );
		if (in_array ( $method, $meta )) {
			return static::$method;
		}
		$underscored = array ('with','query' );
		if (in_array ( $method, $underscored )) {
			return call_user_func_array ( array ($this,'_' . $method ), $parameters );
		}
		if (starts_with ( $method, 'get_' )) {
			return $this->get_attribute ( substr ( $method, 4 ) );
		} elseif (starts_with ( $method, 'set_' )) {
			$this->set_attribute ( substr ( $method, 4 ), $parameters [0] );
		} else {
			return call_user_func_array ( array ($this->query (),$method ), $parameters );
		}
	}

	/**
	 * Dynamically handle static method calls on the model.
	 *
	 * @param string $method
	 * @param array $parameters
	 * @return mixed
	 */
	public static function __callStatic($method, $parameters)
	{
		$model = get_called_class ();
		return call_user_func_array ( [ new $model (),$method ], $parameters );
	}
}