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
namespace Leaps\Base;

use Leaps;

/**
 * Object 是实现 *property* 功能的基类
 *
 * 属性定义了一个getter方法 (如 `getLabel`), 和setter方法(如 `setLabel`)。例如,
 * 以下的getter和setter方法定义一个属性命名 `label`:
 *
 * ~~~
 * private $_label;
 *
 * public function getLabel()
 * {
 *     return $this->_label;
 * }
 *
 * public function setLabel($value)
 * {
 *     $this->_label = $value;
 * }
 * ~~~
 *
 * 属性名称 *case-insensitive*.
 *
 * 可以访问对象的属性，如对象的成员变量。读或写一个属性将导致调用
 * 相应的getter或setter方法。例如,
 *
 * ~~~
 * // equivalent to $label = $object->getLabel();
 * $label = $object->label;
 * // equivalent to $object->setLabel('abc');
 * $object->label = 'abc';
 * ~~~
 *
 * If a property has only a getter method and has no setter method, it is considered as *read-only*. In this case, trying
 * to modify the property value will cause an exception.
 *
 * One can call [[hasProperty()]], [[canGetProperty()]] and/or [[canSetProperty()]] to check the existence of a property.
 *
 * Besides the property feature, Object also introduces an important object initialization life cycle. In particular,
 * creating an new instance of Object or its derived class will involve the following life cycles sequentially:
 *
 * 1. the class constructor is invoked;
 * 2. object properties are initialized according to the given configuration;
 * 3. the `init()` method is invoked.
 *
 * In the above, both Step 2 and 3 occur at the end of the class constructor. It is recommended that
 * you perform object initialization in the `init()` method because at that stage, the object configuration
 * is already applied.
 *
 * In order to ensure the above life cycles, if a child class of Object needs to override the constructor,
 * it should be done like the following:
 *
 * ~~~
 * public function __construct($param1, $param2, ..., $config = [])
 * {
 *     ...
 *     parent::__construct($config);
 * }
 * ~~~
 *
 * That is, a `$config` parameter (defaults to `[]`) should be declared as the last parameter
 * of the constructor, and the parent implementation should be called at the end of the constructor.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Object implements Configurable
{
	/**
	 * 返回该类的完全限定名。
	 * @return string the fully qualified name of this class.
	 */
	public static function className()
	{
		return get_called_class();
	}

	/**
	 * 构造函数。
	 * The default implementation does two things:
	 *
	 * - Initializes the object with the given configuration `$config`.
	 * - Call [[init()]].
	 *
	 * If this method is overridden in a child class, it is recommended that
	 *
	 * - the last parameter of the constructor is a configuration array, like `$config` here.
	 * - call the parent implementation at the end of the constructor.
	 *
	 * @param array $config name-value pairs that will be used to initialize the object properties
	 */
	public function __construct($config = [])
	{
		if (!empty($config)) {
			Leaps::configure($this, $config);
		}
		$this->init();
	}

	/**
	 * 初始化对象
	 * 在该对象初始化结束时调用该方法的给定的配置。
	 */
	public function init()
	{
	}

	/**
	 * Returns the value of an object property.
	 *
	 * Do not call this method directly as it is a PHP magic method that
	 * will be implicitly called when executing `$value = $object->property;`.
	 * @param string $name the property name
	 * @return mixed the property value
	 * @throws UnknownPropertyException if the property is not defined
	 * @throws InvalidCallException if the property is write-only
	 * @see __set()
	 */
	public function __get($name)
	{
		$getter = 'get' . $name;
		if (method_exists($this, $getter)) {
			return $this->$getter();
		} elseif (method_exists($this, 'set' . $name)) {
			throw new InvalidCallException('Getting write-only property: ' . get_class($this) . '::' . $name);
		} else {
			throw new UnknownPropertyException('Getting unknown property: ' . get_class($this) . '::' . $name);
		}
	}

	/**
	 * 设置对象属性的值。
	 *
	 * Do not call this method directly as it is a PHP magic method that
	 * will be implicitly called when executing `$object->property = $value;`.
	 * @param string $name the property name or the event name
	 * @param mixed $value the property value
	 * @throws UnknownPropertyException if the property is not defined
	 * @throws InvalidCallException if the property is read-only
	 * @see __get()
	 */
	public function __set($name, $value)
	{
		$setter = 'set' . $name;
		if (method_exists($this, $setter)) {
			$this->$setter($value);
		} elseif (method_exists($this, 'get' . $name)) {
			throw new InvalidCallException('Setting read-only property: ' . get_class($this) . '::' . $name);
		} else {
			throw new UnknownPropertyException('Setting unknown property: ' . get_class($this) . '::' . $name);
		}
	}

	/**
	 * 判断属性是否是null
	 *
	 * Do not call this method directly as it is a PHP magic method that
	 * will be implicitly called when executing `isset($object->property)`.
	 *
	 * Note that if the property is not defined, false will be returned.
	 * @param string $name the property name or the event name
	 * @return boolean whether the named property is set (not null).
	 * @see http://php.net/manual/en/function.isset.php
	 */
	public function __isset($name)
	{
		$getter = 'get' . $name;
		if (method_exists($this, $getter)) {
			return $this->$getter() !== null;
		} else {
			return false;
		}
	}

	/**
	 * 设置对象属性为 null.
	 *
	 * Do not call this method directly as it is a PHP magic method that
	 * will be implicitly called when executing `unset($object->property)`.
	 *
	 * Note that if the property is not defined, this method will do nothing.
	 * If the property is read-only, it will throw an exception.
	 * @param string $name the property name
	 * @throws InvalidCallException if the property is read only.
	 * @see http://php.net/manual/en/function.unset.php
	 */
	public function __unset($name)
	{
		$setter = 'set' . $name;
		if (method_exists($this, $setter)) {
			$this->$setter(null);
		} elseif (method_exists($this, 'get' . $name)) {
			throw new InvalidCallException('Unsetting read-only property: ' . get_class($this) . '::' . $name);
		}
	}

	/**
	 * Calls the named method which is not a class method.
	 *
	 * Do not call this method directly as it is a PHP magic method that
	 * will be implicitly called when an unknown method is being invoked.
	 * @param string $name the method name
	 * @param array $params method parameters
	 * @throws UnknownMethodException when calling unknown method
	 * @return mixed the method return value
	 */
	public function __call($name, $params)
	{
		throw new UnknownMethodException('Calling unknown method: ' . get_class($this) . "::$name()");
	}

	/**
	 * 判断属性是否存在
	 * A property is defined if:
	 *
	 * - the class has a getter or setter method associated with the specified name
	 *   (in this case, property name is case-insensitive);
	 * - the class has a member variable with the specified name (when `$checkVars` is true);
	 *
	 * @param string $name the property name
	 * @param boolean $checkVars whether to treat member variables as properties
	 * @return boolean whether the property is defined
	 * @see canGetProperty()
	 * @see canSetProperty()
	 */
	public function hasProperty($name, $checkVars = true)
	{
		return $this->canGetProperty($name, $checkVars) || $this->canSetProperty($name, false);
	}

	/**
	 * 判断属性是否可读
	 * A property is readable if:
	 *
	 * - the class has a getter method associated with the specified name
	 *   (in this case, property name is case-insensitive);
	 * - the class has a member variable with the specified name (when `$checkVars` is true);
	 *
	 * @param string $name the property name
	 * @param boolean $checkVars whether to treat member variables as properties
	 * @return boolean whether the property can be read
	 * @see canSetProperty()
	 */
	public function canGetProperty($name, $checkVars = true)
	{
		return method_exists($this, 'get' . $name) || $checkVars && property_exists($this, $name);
	}

	/**
	 * 判断属性是否可写
	 * A property is writable if:
	 *
	 * - the class has a setter method associated with the specified name
	 *   (in this case, property name is case-insensitive);
	 * - the class has a member variable with the specified name (when `$checkVars` is true);
	 *
	 * @param string $name the property name
	 * @param boolean $checkVars whether to treat member variables as properties
	 * @return boolean whether the property can be written
	 * @see canGetProperty()
	 */
	public function canSetProperty($name, $checkVars = true)
	{
		return method_exists($this, 'set' . $name) || $checkVars && property_exists($this, $name);
	}

	/**
	 * 判断是否定义了指定的方法
	 *
	 * The default implementation is a call to php function `method_exists()`.
	 * You may override this method when you implemented the php magic method `__call()`.
	 * @param string $name the method name
	 * @return boolean whether the method is defined
	 */
	public function hasMethod($name)
	{
		return method_exists($this, $name);
	}
}
