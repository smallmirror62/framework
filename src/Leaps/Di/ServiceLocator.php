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
use Closure;
use Leaps\Base\Service;
use Leaps\Base\InvalidConfigException;

/**
 * ServiceLocator implements a [service locator](http://en.wikipedia.org/wiki/Service_locator_pattern).
 *
 * To use ServiceLocator, you first need to register component IDs with the corresponding component
 * definitions with the locator by calling [[set()]] or [[setComponents()]].
 * You can then call [[get()]] to retrieve a component with the specified ID. The locator will automatically
 * instantiate and configure the component according to the definition.
 *
 * For example,
 *
 * ```php
 * $locator = new \Leaps\Di\ServiceLocator;
 * $locator->setComponents([
 * 'db' => [
 * 'class' => 'Leaps\Db\Connection',
 * 'dsn' => 'sqlite:path/to/file.db',
 * ],
 * 'cache' => [
 * 'class' => 'Leaps\Cache\DbCache',
 * 'db' => 'db',
 * ],
 * ]);
 *
 * $db = $locator->get('db'); // or $locator->db
 * $cache = $locator->get('cache'); // or $locator->cache
 * ```
 *
 * Because [[\Leaps\Base\Module]] extends from ServiceLocator, modules and the application are all service locators.
 *
 * @property array $services The list of the component definitions or the loaded component instances (ID =>
 *           definition or instance).
 *          
 */
class ServiceLocator extends Service
{
	/**
	 *
	 * @var array shared component instances indexed by their IDs
	 */
	private $_services = [ ];
	/**
	 *
	 * @var array component definitions indexed by their IDs
	 */
	private $_definitions = [ ];
	
	/**
	 * Getter magic method.
	 * This method is overridden to support accessing components like reading properties.
	 *
	 * @param string $name component or property name
	 * @return mixed the named property value
	 */
	public function __get($name)
	{
		if ($this->has ( $name )) {
			return $this->get ( $name );
		} else {
			return parent::__get ( $name );
		}
	}
	
	/**
	 * Checks if a property value is null.
	 * This method overrides the parent implementation by checking if the named component is loaded.
	 *
	 * @param string $name the property name or the event name
	 * @return boolean whether the property value is null
	 */
	public function __isset($name)
	{
		if ($this->has ( $name, true )) {
			return true;
		} else {
			return parent::__isset ( $name );
		}
	}
	
	/**
	 * Returns a value indicating whether the locator has the specified component definition or has instantiated the component.
	 * This method may return different results depending on the value of `$checkInstance`.
	 *
	 * - If `$checkInstance` is false (default), the method will return a value indicating whether the locator has the specified
	 * component definition.
	 * - If `$checkInstance` is true, the method will return a value indicating whether the locator has
	 * instantiated the specified component.
	 *
	 * @param string $id component ID (e.g. `db`).
	 * @param boolean $checkInstance whether the method should check if the component is shared and instantiated.
	 * @return boolean whether the locator has the specified component definition or has instantiated the component.
	 * @see set()
	 */
	public function has($id, $checkInstance = false)
	{
		return $checkInstance ? isset ( $this->_services [$id] ) : isset ( $this->_definitions [$id] );
	}
	
	/**
	 * Returns the component instance with the specified ID.
	 *
	 * @param string $id component ID (e.g. `db`).
	 * @param boolean $throwException whether to throw an exception if `$id` is not registered with the locator before.
	 * @return object|null the component of the specified ID. If `$throwException` is false and `$id`
	 *         is not registered before, null will be returned.
	 * @throws InvalidConfigException if `$id` refers to a nonexistent component ID
	 * @see has()
	 * @see set()
	 */
	public function get($id, $throwException = true)
	{
		if (isset ( $this->_services [$id] )) {
			return $this->_services [$id];
		}
		
		if (isset ( $this->_definitions [$id] )) {
			$definition = $this->_definitions [$id];
			if (is_object ( $definition ) && ! $definition instanceof Closure) {
				return $this->_services [$id] = $definition;
			} else {
				return $this->_services [$id] = Leaps::createObject ( $definition );
			}
		} elseif ($throwException) {
			throw new InvalidConfigException ( "Unknown component ID: $id" );
		} else {
			return null;
		}
	}
	
	/**
	 * Registers a component definition with this locator.
	 *
	 * For example,
	 *
	 * ```php
	 * // a class name
	 * $locator->set('cache', 'leaps \caching\FileCache');
	 *
	 * // a configuration array
	 * $locator->set('db', [
	 * 'class' => 'leaps \db\Connection',
	 * 'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
	 * 'username' => 'root',
	 * 'password' => '',
	 * 'charset' => 'utf8',
	 * ]);
	 *
	 * // an anonymous function
	 * $locator->set('cache', function ($params) {
	 * return new \leaps \caching\FileCache;
	 * });
	 *
	 * // an instance
	 * $locator->set('cache', new \leaps \caching\FileCache);
	 * ```
	 *
	 * If a component definition with the same ID already exists, it will be overwritten.
	 *
	 * @param string $id component ID (e.g. `db`).
	 * @param mixed $definition the component definition to be registered with this locator.
	 *        It can be one of the following:
	 *       
	 *        - a class name
	 *        - a configuration array: the array contains name-value pairs that will be used to
	 *        initialize the property values of the newly created object when [[get()]] is called.
	 *        The `class` element is required and stands for the the class of the object to be created.
	 *        - a PHP callable: either an anonymous function or an array representing a class method (e.g. `['Foo', 'bar']`).
	 *        The callable will be called by [[get()]] to return an object associated with the specified component ID.
	 *        - an object: When [[get()]] is called, this object will be returned.
	 *       
	 * @throws InvalidConfigException if the definition is an invalid configuration array
	 */
	public function set($id, $definition)
	{
		if ($definition === null) {
			unset ( $this->_services [$id], $this->_definitions [$id] );
			return;
		}
		
		unset ( $this->_services [$id] );
		
		if (is_object ( $definition ) || is_callable ( $definition, true )) {
			// an object, a class name, or a PHP callable
			$this->_definitions [$id] = $definition;
		} elseif (is_array ( $definition )) {
			// a configuration array
			if (isset ( $definition ['className'] )) {
				$this->_definitions [$id] = $definition;
			} else {
				throw new InvalidConfigException ( "The configuration for the \"$id\" service must contain a \"className\" element." );
			}
		} else {
			throw new InvalidConfigException ( "Unexpected configuration type for the \"$id\" service: " . gettype ( $definition ) );
		}
	}
	
	/**
	 * Removes the component from the locator.
	 *
	 * @param string $id the component ID
	 */
	public function clear($id)
	{
		unset ( $this->_definitions [$id], $this->_services [$id] );
	}
	
	/**
	 * Returns the list of the component definitions or the loaded component instances.
	 *
	 * @param boolean $returnDefinitions whether to return component definitions instead of the loaded component instances.
	 * @return array the list of the component definitions or the loaded component instances (ID => definition or instance).
	 */
	public function getServices($returnDefinitions = true)
	{
		return $returnDefinitions ? $this->_definitions : $this->_services;
	}
	
	/**
	 * Registers a set of component definitions in this locator.
	 *
	 * This is the bulk version of [[set()]]. The parameter should be an array
	 * whose keys are component IDs and values the corresponding component definitions.
	 *
	 * For more details on how to specify component IDs and definitions, please refer to [[set()]].
	 *
	 * If a component definition with the same ID already exists, it will be overwritten.
	 *
	 * The following is an example for registering two component definitions:
	 *
	 * ```php
	 * [
	 * 'db' => [
	 * 'class' => 'Leaps\Db\Connection',
	 * 'dsn' => 'sqlite:path/to/file.db',
	 * ],
	 * 'cache' => [
	 * 'class' => 'Leaps\Cache\DbCache',
	 * 'db' => 'db',
	 * ],
	 * ]
	 * ```
	 *
	 * @param array $services service definitions or instances
	 */
	public function setServices($services)
	{
		foreach ( $services as $id => $service ) {
			$this->set ( $id, $service );
		}
	}
}
