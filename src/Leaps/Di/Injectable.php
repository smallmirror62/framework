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

use Leaps\Core\Base;

/**
 * Leaps\Di\Injectable
 *
 * This class allows to access services in the services container by just only accessing a public property
 * with the same name of a registered service
 */
abstract class Injectable extends Base implements InjectionAwareInterface
{

	/**
	 * 依赖注入器
	 *
	 * @var Leaps\Di\ContainerInterface
	 */
	protected $_dependencyInjector;

	/**
	 * 构造方法 默认的实现做了两件事:
	 * - 使用给定的初始化对象配置。 - Call [[init()]].
	 * 如果在子类重写此方法,建议调用父实现的构造函数。
	 *
	 * @param array $config 名称-值对将用于初始化对象属性
	 */
	public function __construct($config = [])
	{
		if (! empty ( $config ) && is_array ( $config )) {
			foreach ( $config as $name => $value ) {
				$this->$name = $value;
			}
		}
	}

	/**
	 * 初始化对象
	 * 重写Base类，为了先将Di实例射进来在执行init
	 */
	public function init()
	{
	}

	/**
	 * 设置依赖注入器
	 *
	 * @param \Leaps\Di\ContainerInterface dependencyInjector
	 */
	public function setDI(ContainerInterface $dependencyInjector)
	{
		if (! is_object ( $dependencyInjector )) {
			throw new Exception ( "Dependency Injector is invalid" );
		}
		$this->_dependencyInjector = $dependencyInjector;
	}

	/**
	 * 返回依赖注入器实例
	 *
	 * @return \Leaps\Di\ContainerInterface
	 */
	public function getDI()
	{
		if (! is_object ( $this->_dependencyInjector )) {
			$this->_dependencyInjector = Container::getDefault ();
		}
		return $this->_dependencyInjector;
	}
}