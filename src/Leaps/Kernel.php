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
}