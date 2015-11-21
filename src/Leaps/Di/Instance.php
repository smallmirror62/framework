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
namespace Leaps\Di;

use Leaps;
use Leaps\Base\InvalidConfigException;

/**
 * Instance represents a reference to a named object in a dependency injection (DI) container or a service locator.
 *
 * You may use [[get()]] to obtain the actual object referenced by [[id]].
 *
 * Instance is mainly used in two places:
 *
 * - When configuring a dependency injection container, you use Instance to reference a class name, interface name
 * or alias name. The reference can later be resolved into the actual object by the container.
 * - In classes which use service locator to obtain dependent objects.
 *
 * The following example shows how to configure a DI container with Instance:
 *
 * ```php
 * $container = new \Leaps\Di\Container;
 * $container->set('cache', 'leaps \caching\DbCache', Instance::of('db'));
 * $container->set('db', [
 * 'className' => 'Leaps\Db\Connection',
 * 'dsn' => 'sqlite:path/to/file.db',
 * ]);
 * ```
 *
 * And the following example shows how a class retrieves a component from a service locator:
 *
 * ```php
 * class DbCache extends Cache
 * {
 * public $db = 'db';
 *
 * public function init()
 * {
 * parent::init();
 * $this->db = Instance::ensure($this->db, 'Leaps\Db\Connection');
 * }
 * }
 * ```
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Instance
{
	/**
	 *
	 * @var string the component ID, class name, interface name or alias name
	 */
	public $id;
	
	/**
	 * Constructor.
	 *
	 * @param string $id the component ID
	 */
	protected function __construct($id)
	{
		$this->id = $id;
	}
	
	/**
	 * Creates a new Instance object.
	 *
	 * @param string $id the component ID
	 * @return Instance the new Instance object.
	 */
	public static function of($id)
	{
		return new static ( $id );
	}
	
	/**
	 * Resolves the specified reference into the actual object and makes sure it is of the specified type.
	 *
	 * The reference may be specified as a string or an Instance object. If the former,
	 * it will be treated as a component ID, a class/interface name or an alias, depending on the container type.
	 *
	 * If you do not specify a container, the method will first try `Leaps::$app` followed by `Leaps::$container`.
	 *
	 * For example,
	 *
	 * ```php
	 * use leaps \db\Connection;
	 *
	 * // returns Leaps::$app->db
	 * $db = Instance::ensure('db', Connection::className());
	 * // returns an instance of Connection using the given configuration
	 * $db = Instance::ensure(['dsn' => 'sqlite:path/to/my.db'], Connection::className());
	 * ```
	 *
	 * @param object|string|array|static $reference an object or a reference to the desired object.
	 *        You may specify a reference in terms of a component ID or an Instance object.
	 *        Starting from version 2.0.2, you may also pass in a configuration array for creating the object.
	 *        If the "class" value is not specified in the configuration array, it will use the value of `$type`.
	 * @param string $type the class/interface name to be checked. If null, type check will not be performed.
	 * @param ServiceLocator|Container $container the container. This will be passed to [[get()]].
	 * @return object the object referenced by the Instance, or `$reference` itself if it is an object.
	 * @throws InvalidConfigException if the reference is invalid
	 */
	public static function ensure($reference, $type = null, $container = null)
	{
		if ($reference instanceof $type) {
			return $reference;
		} elseif (is_array ( $reference )) {
			$class = isset ( $reference ['className'] ) ? $reference ['className'] : $type;
			if (! $container instanceof Container) {
				$container = Leaps::$container;
			}
			unset ( $reference ['className'] );
			return $container->get ( $class, [ ], $reference );
		} elseif (empty ( $reference )) {
			throw new InvalidConfigException ( 'The required component is not specified.' );
		}
		
		if (is_string ( $reference )) {
			$reference = new static ( $reference );
		}
		
		if ($reference instanceof self) {
			$component = $reference->get ( $container );
			if ($component instanceof $type || $type === null) {
				return $component;
			} else {
				throw new InvalidConfigException ( '"' . $reference->id . '" refers to a ' . get_class ( $component ) . " component. $type is expected." );
			}
		}
		
		$valueType = is_object ( $reference ) ? get_class ( $reference ) : gettype ( $reference );
		throw new InvalidConfigException ( "Invalid data type: $valueType. $type is expected." );
	}
	
	/**
	 * Returns the actual object referenced by this Instance object.
	 *
	 * @param ServiceLocator|Container $container the container used to locate the referenced object.
	 *        If null, the method will first try `Leaps::$app` then `Leaps::$container`.
	 * @return object the actual object referenced by this Instance object.
	 */
	public function get($container = null)
	{
		if ($container) {
			return $container->get ( $this->id );
		}
		if (Leaps::$app && Leaps::$app->has ( $this->id )) {
			return Leaps::$app->get ( $this->id );
		} else {
			return Leaps::$container->get ( $this->id );
		}
	}
}
