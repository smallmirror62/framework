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
namespace Leaps;

use Leaps\Core\InvalidConfigException;
use Leaps\Core\UnknownClassException;
use Leaps\Core\InvalidParamException;
class Kernel
{
	/**
	 * 测试环境
	 *
	 * @var string constant used for when in testing mode
	 */
	const TEST = 'test';

	/**
	 * 开发环境
	 *
	 * @var string
	 */
	const DEVELOPMENT = 'development';

	/**
	 * 生产环境
	 *
	 * @var string
	 */
	const PRODUCTION = 'production';

	/**
	 * 框架执行环境
	 *
	 * @var string
	 */
	public static $env = Kernel::PRODUCTION;

	/**
	 * 路径别名集合
	 *
	 * @var array
	 */
	private static $_aliases = [ ];

	/**
	 * classMap
	 *
	 * @var array
	*/
	private static $_classMap = [ ];

	/**
	 * 应用实例
	 *
	 * @var Leaps\Core\Application
	 */
	private static $_application;

	/**
	 * 创建新的对象
	 * 直接传类名来创建对象
	 * \Leaps\Kernel::createObject('Leaps\HttpClient\Adapter\Curl');
	 * //直接传匿名方法来创建支持参数
	 * \Leaps\Kernel::createObject(function(){
	 * return new \Leaps\HttpClient\Adapter\Curl();
	 * },[]);
	 * 使用类构造方法来创建对象
	 * \Leaps\Kernel::createObject(['className'=>'Leaps\HttpClient\Adapter\Curl','hostIp'=>'127.0.0.1']);
	 *
	 * @param string/array $definition
	 * @param array $parameters
	 * @throws InvalidConfigException
	 * @return object
	 */
	public static function createObject($definition, $parameters = [], $throwException = true)
	{
		$instance = null;
		if (is_string ( $definition )) {
			if (class_exists ( $definition )) {
				$reflection = new \ReflectionClass ( $definition );
				if (is_array ( $parameters )) {
					$instance = $reflection->newInstanceArgs ( $parameters );
				} else {
					$instance = $reflection->newInstance ();
				}
			}
		} elseif (is_object ( $definition )) {
			if ($definition instanceof \Closure) {
				if (is_array ( $definition )) {
					$instance = call_user_func_array ( $definition, $parameters );
				} else {
					$instance = call_user_func ( $definition );
				}
			} else {
				$instance = $definition;
			}
		} elseif (is_array ( $definition ) && isset ( $definition ['className'] )) {
			$className = $definition ['className'];
			unset ( $definition ['className'] );
			$reflection = new \ReflectionClass ( $className );
			if (! empty ( $parameters )) { // 模块初始化
				$parameters [] = $definition;
				$instance = $reflection->newInstanceArgs ( $parameters );
			} else {
				if (empty ( $definition )) {
					$instance = $reflection->newInstance ();
				} else {
					$instance = $reflection->newInstanceArgs ( [ $definition ] );
				}
			}
		} elseif (is_array ( $definition ) && $throwException) {
			throw new InvalidConfigException ( 'Object configuration must be an array containing a "className" element.' );
		} elseif ($throwException) {
			throw new InvalidConfigException ( "Unsupported configuration type: " . gettype ( $definition ) );
		}

		/**
		 * 如果实现了 \Leaps\Di\InjectionAwareInterface 就把DI实例射进去
		 */
		if (is_object ( $instance ) && method_exists ( $instance, "setDI" )) {
			$instance->setDI ( static::getDi () );
		}

		return $instance;
	}

	/**
	 * 返回Di容器实例
	 */
	public static function getDi()
	{
		return \Leaps\Di\Container::getDefault ();
	}

	/**
	 * 自动装载器
	 *
	 * @param string $className 类的完全限定名称
	 */
	public static function autoload($className)
	{
		if (isset ( static::$_classMap [$className] )) {
			$classFile = static::$_classMap [$className];
			if ($classFile [0] === '@') {
				$classFile = static::getAlias ( $classFile );
			}
		} elseif (strpos ( $className, '\\' ) !== false) {
			$classFile = static::getAlias ( '@' . str_replace ( '\\', '/', $className ) . '.php', false );
			if ($classFile === false || ! is_file ( $classFile )) {
				return;
			}
		} else {
			return;
		}
		include ($classFile);
		if (static::$env == static::DEVELOPMENT && ! class_exists ( $className, false ) && ! interface_exists ( $className, false ) && ! trait_exists ( $className, false )) {
			throw new UnknownClassException ( "Unable to find '$className' in file: $classFile. Namespace missing?" );
		}
	}

	/**
	 * 注册一个路径别名。
	 *
	 * @throws InvalidParamException 如果路径是无效的别名
	 * @see getAlias()
	 */
	public static function setAlias($alias, $path)
	{
		if (strncmp ( $alias, '@', 1 )) {
			$alias = '@' . $alias;
		}
		$pos = strpos ( $alias, '/' );
		$root = $pos === false ? $alias : substr ( $alias, 0, $pos );
		if ($path !== null) {
			$path = strncmp ( $path, '@', 1 ) ? rtrim ( $path, '\\/' ) : static::getAlias ( $path );
			if (! isset ( static::$_aliases [$root] )) {
				if ($pos === false) {
					static::$_aliases [$root] = $path;
				} else {
					static::$_aliases [$root] = [ $alias => $path ];
				}
			} elseif (is_string ( static::$_aliases [$root] )) {
				if ($pos === false) {
					static::$_aliases [$root] = $path;
				} else {
					static::$_aliases [$root] = [ $alias => $path,$root => static::$_aliases [$root] ];
				}
			} else {
				static::$_aliases [$root] [$alias] = $path;
				krsort ( static::$_aliases [$root] );
			}
		} elseif (isset ( static::$_aliases [$root] )) {
			if (is_array ( static::$_aliases [$root] )) {
				unset ( static::$_aliases [$root] [$alias] );
			} elseif ($pos === false) {
				unset ( static::$_aliases [$root] );
			}
		}
	}

	/**
	 * 解析路径别名，并返回路径的详情
	 *
	 * @param string $alias 要翻译的别名
	 * @param boolean $throwException 是否抛出异常,如果给定的别名是无效的。 如果这是错误和无效的别名,该方法将返回错误。
	 * @return string boolean
	 * @throws InvalidParamException 如果别名无效$throwException为true
	 * @see setAlias()
	 */
	public static function getAlias($alias, $throwException = true)
	{
		if (strncmp ( $alias, '@', 1 )) { // 不是一个别名
			return $alias;
		}
		$pos = strpos ( $alias, '/' );
		$root = $pos === false ? $alias : substr ( $alias, 0, $pos );
		if (isset ( static::$_aliases [$root] )) {
			if (is_string ( static::$_aliases [$root] )) {
				return $pos === false ? static::$_aliases [$root] : static::$_aliases [$root] . substr ( $alias, $pos );
			} else {
				foreach ( static::$_aliases [$root] as $name => $path ) {
					if (strpos ( $alias . '/', $name . '/' ) === 0) {
						return $path . substr ( $alias, strlen ( $name ) );
					}
				}
			}
		}
		if ($throwException) {
			throw new InvalidParamException ( "Invalid path alias: $alias" );
		} else {
			return false;
		}
	}

	/**
	 * 返回路径别名的路径信息
	 * 如果别名匹配多个根将返回别名最长的一个。
	 *
	 * @param string $alias 别名
	 * @return string/boolean 跟别名或false
	 */
	public static function getRootAlias($alias)
	{
		$pos = strpos ( $alias, '/' );
		$root = $pos === false ? $alias : substr ( $alias, 0, $pos );
		if (isset ( static::$_aliases [$root] )) {
			if (is_string ( static::$_aliases [$root] )) {
				return $root;
			} else {
				foreach ( static::$_aliases [$root] as $name => $path ) {
					if (strpos ( $alias . '/', $name . '/' ) === 0) {
						return $name;
					}
				}
			}
		}
		return false;
	}

	/**
	 * 获取classMap
	 *
	 * @return multitype:
	 */
	public static function getClassMap($className = '')
	{
		if ('' === $className) {
			return static::$_classMap;
		} elseif (isset ( static::$_classMap [$className] )) {
			return static::$_classMap [$className];
		} else {
			return null;
		}
	}

	/**
	 * 注册classMap
	 *
	 * @param array $classMap 类文件名映射
	 */
	public static function addClassMap($className, $map = '')
	{
		if (is_array ( $className )) {
			static::$_classMap = array_merge ( static::$_classMap, $className );
		} else {
			static::$_classMap [$className] = $map;
		}
	}

	/**
	 * 获取一个值，如果是匿名方法就获取方法的结果
	 *
	 * @param mixed $var The value to get
	 * @return mixed
	 */
	public static function value($var)
	{
		return ($var instanceof \Closure) ? $var () : $var;
	}

	/**
	 * 得到一个类或对象的名字
	 *
	 * @param object|string $class 类或对象
	 * @return string
	 */
	public static function classBasename($class)
	{
		if (is_object ( $class ))
			$class = get_class ( $class );
		return basename ( str_replace ( '\\', '/', $class ) );
	}

	/**
	 * 配置一个对象的初始属性值。
	 *
	 * @param object $object 对象配置
	 * @param array $properties 属性初始值给定的名称-值对。
	 */
	public static function configure($object, $properties)
	{
		foreach ( $properties as $name => $value ) {
			$object->$name = $value;
		}
	}

	/**
	 * 返回对象的公共成员变量。
	 *
	 * @param object $object 处理的对象
	 * @return array 对象的公共成员变量
	 */
	public static function getObjectVars($object)
	{
		return get_object_vars ( $object );
	}

	/**
	 * 获取框架版本
	 *
	 * @return string the version of Leaps framework
	 */
	public static function getVersion()
	{
		return Version::get ();
	}

	/**
	 * 返回HTML超链接可以显示在您的网页显示 "Powered by Leaps Framework" 信息。
	 *
	 * @return string an HTML hyperlink that can be displayed on your Web page showing "Powered by Leaps Framework" information
	 */
	public static function powered()
	{
		return 'Powered by <a href="http://www.tintsoft.com/" rel="external">Leaps Framework</a>';
	}
}