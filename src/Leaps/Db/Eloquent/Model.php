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

use Leaps\Db\Db;
use Leaps\Utility\Str;

abstract class Model
{

	/**
	 * 模型属性
	 *
	 * @var array
	 */
	public $attributes = [ ];

	/**
	 * 原始模型属性
	 *
	 * @var array
	 */
	public $original = [ ];

	/**
	 * 已经加载的查询关系
	 *
	 * @var array
	 */
	public $relationship = [ ];

	/**
	 * 数据库中是否存在该模型
	 *
	 * @var bool
	 */
	public $exists = false;

	/**
	 * 模型关系
	 *
	 * @var array
	 */
	public $include = [ ];

	/**
	 * 主键名称
	 *
	 * @var string
	 */
	public static $key = 'id';

	/**
	 * 可批量赋值的属性
	 *
	 * @var array
	 */
	public static $fillable;

	/**
	 * 隐藏的字段
	 *
	 * @var array
	 */
	public static $hidden = [ ];

	/**
	 * 自动创建模型创建和更新时间戳
	 *
	 * @var bool
	 */
	public static $timestamps = true;

	/**
	 * 模型表明
	 *
	 * @var string
	 */
	public static $table;

	/**
	 * 模型的数据库连接
	 *
	 * @var string
	 */
	public static $connection;

	/**
	 * 与模型关联序列的名称
	 *
	 * @var string
	 */
	public static $sequence;

	/**
	 * 默认每页的分页数量
	 *
	 * @var int
	 */
	public static $perPage = 20;

	/**
	 * 构造方法
	 *
	 * @param array $attribute 属性
	 * @param bool $exist 是否存在
	 * @return void
	 */
	public function __construct($attributes = [], $exist = false)
	{
		$this->exist = $exist;
		$this->fill ( $attributes );
	}

	/**
	 * 用一个数组来模拟模型
	 *
	 * @param array $attributes 属性数组
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
			if (is_array ( static::$fillable )) {
				if (in_array ( $key, static::$fillable )) {
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
	 * 用数组内容填充模型
	 *
	 * @param array $attributes 属性数组
	 * @return Model
	 */
	public function fillRaw(array $attributes)
	{
		return $this->fill ( $attributes, true );
	}

	/**
	 * 设置模型的可访问属性
	 *
	 * @param array $attributes 属性
	 * @return void
	 */
	public static function accessible($attributes = null)
	{
		if (is_null ( $attributes )) {
			return static::$fillable;
		}
		static::$fillable = $attributes;
	}

	/**
	 * 创建一个新的模型到数据库
	 *
	 * @param array $attributes 模型属性
	 * @return Model|false 如果成功返回模型实例，否者返回false
	 */
	public static function create($attributes)
	{
		$model = new static ( $attributes );
		$success = $model->save ();
		return ($success) ? $model : false;
	}

	/**
	 * 更新模型实例到数据库
	 *
	 * @param mixed $id 主键ID
	 * @param array $attributes 模型属性
	 * @return int
	 */
	public static function update($id, $attributes)
	{
		$model = new static ( [ ], true );
		$model->fill ( $attributes );
		if (static::$timestamps) {
			$model->timestamp ();
		}
		return $model->query ()->where ( $model->key (), '=', $id )->update ( $model->attributes );
	}

	/**
	 * 从数据库获取所有模型
	 *
	 * @return array
	 */
	public static function all()
	{
		$res = new static ();
		return $res->query ()->get ();
	}

	/**
	 * 设置查询关系
	 *
	 * @param array $includes
	 * @return Model
	 */
	public function _with($includes)
	{
		$this->include = ( array ) $includes;
		return $this;
	}

	/**
	 * 获得一对一关联的查询
	 *
	 * @param string $model 模型
	 * @param string $foreign
	 * @return Relationship
	 */
	public function hasOne($model, $foreign = null)
	{
		return $this->hasOneOrMany ( __FUNCTION__, $model, $foreign );
	}

	/**
	 * 获得一对多关联的查询
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
		if ($type == 'hasOne') {
			return new \Leaps\Db\Eloquent\Relationship\HasOne ( $this, $model, $foreign );
		} else {
			return new \Leaps\Db\Eloquent\Relationship\HasMany ( $this, $model, $foreign );
		}
	}

	/**
	 * 得到一对一的查询（反向）关系
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
		return new \Leaps\Db\Eloquent\Relationship\BelongsTo ( $this, $model, $foreign );
	}

	/**
	 * 获取一个多对多关系的查询
	 *
	 * @param string $model
	 * @param string $table
	 * @param string $foreign
	 * @param string $other
	 * @return Has_Many_And_Belongs_To
	 */
	public function HasManyAndBelongsTo($model, $table = null, $foreign = null, $other = null)
	{
		return new \Leaps\Db\Eloquent\Relationship\HasManyAndBelongsTo ( $this, $model, $table, $foreign, $other );
	}

	/**
	 * 保存模型及其所有关系到数据库
	 *
	 * @return bool
	 */
	public function push()
	{
		$this->save ();
		foreach ( $this->relationship as $name => $models ) {
			if (! is_array ( $models )) {
				$models = [ $models ];
			}
			foreach ( $models as $model ) {
				$model->push ();
			}
		}
	}

	/**
	 * 保存模型实例到数据库
	 *
	 * @return bool
	 */
	public function save()
	{
		if (! $this->dirty ()) {
			return true;
		}
		if (static::$timestamps) {
			$this->timestamp ();
		}
		$this->fireEvent ( 'saving' );
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
	 * 从数据库删除模型
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
	 * 设置模型的更新和创建时间
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
	 * 更新模型的时间戳并立即保存
	 *
	 * @return void
	 */
	public function touch()
	{
		$this->timestamp ();
		$this->save ();
	}

	/**
	 * 获取模型的一个新的流利查询生成器实例
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
		return $this->attributes [$attribute] != $this->original [$attribute];
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
		return static::$table ?  : strtolower ( Str::plural ( \Leaps\Kernel::classBasename ( $this ) ) );
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
	 * 获取模型主键
	 *
	 * @return int
	 */
	public function getKey()
	{
		return $this->attributes [static::$key];
	}

	/**
	 * 设置模型主键
	 *
	 * @param int $value
	 * @return void
	 */
	public function setKey($value)
	{
		return $this->setAttribute ( static::$key, $value );
	}

	/**
	 * 获取模型属性
	 *
	 * @param string $key
	 */
	public function getAttribute($key)
	{
		return $this->attributes [$key];
	}

	/**
	 * 设置模型属性
	 *
	 * @param string $key 名称
	 * @param mixed $value 值
	 * @return void
	 */
	public function setAttribute($key, $value)
	{
		$this->attributes [$key] = $value;
	}

	/**
	 * 从模型中删除属性
	 *
	 * @param string $key
	 */
	final public function purge($key)
	{
		unset ( $this->original [$key] );
		unset ( $this->attributes [$key] );
	}

	/**
	 * 获取数组形式的模型属性和关系
	 *
	 * @return array
	 */
	public function toArray()
	{
		$attributes = [ ];
		foreach ( array_keys ( $this->attributes ) as $attribute ) {
			if (! in_array ( $attribute, static::$hidden )) {
				$attributes [$attribute] = $this->$attribute;
			}
		}

		foreach ( $this->relationship as $name => $models ) {
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
	 * 触发模型事件
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
	 * 处理属性和关联的动态检索
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __get($key)
	{
		if (array_key_exists ( $key, $this->relationship )) {
			return $this->relationship [$key];
		} elseif (array_key_exists ( $key, $this->attributes )) {
			return $this->{"get{$key}"} ();
		} elseif (method_exists ( $this, $key )) {
			return $this->relationship [$key] = $this->$key ()->results ();
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
		$this->{"set{$key}"} ( $value );
	}

	/**
	 * Determine if an attribute exists on the model.
	 *
	 * @param string $key
	 * @return bool
	 */
	public function __isset($key)
	{
		foreach ( [ 'attributes','relationship' ] as $source ) {
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
		foreach ( array ('attributes','relationship' ) as $source ) {
			unset ( $this->{$source} [$key] );
		}
	}

	/**
	 * 动态调用模型方法
	 *
	 * @param string $method
	 * @param array $parameters
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
		$meta = [ 'key','table','connection','sequence','perPage','timestamps' ];
		if (in_array ( $method, $meta )) {
			return static::$method;
		}
		$underscored = [ 'with','query' ];
		if (in_array ( $method, $underscored )) {
			return call_user_func_array ( [ $this,'_' . $method ], $parameters );
		}
		if (Str::startsWith ( $method, 'get' )) {
			return $this->getAttribute ( substr ( $method, 3 ) );
		} elseif (Str::startsWith ( $method, 'set' )) {
			$this->setAttribute ( substr ( $method, 3 ), $parameters [0] );
		} else {
			return call_user_func_array ( [ $this->query (),$method ], $parameters );
		}
	}

	/**
	 * 静态调用模型动态方法
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