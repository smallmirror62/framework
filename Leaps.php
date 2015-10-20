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
/**
 * 记录应用开始时间
 */
defined ( 'LEAPS_BEGIN_TIME' ) or define ( 'LEAPS_BEGIN_TIME', microtime ( true ) );

/**
 * 记录内存初始使用
 */
defined ( 'LEAPS_BEGIN_MEM' ) or define ( 'LEAPS_BEGIN_MEM', memory_get_usage () );

/**
 * This constant defines the framework installation directory.
 */
defined ( 'LEAPS_PATH' ) or define ( 'LEAPS_PATH', __DIR__ );

/**
 * This constant defines whether the application should be in debug mode or not.
 * Defaults to false.
 */
defined ( 'LEAPS_DEBUG' ) or define ( 'LEAPS_DEBUG', false );

/**
 * This constant defines in which environment the application is running. Defaults to 'prod', meaning production environment.
 * You may define this constant in the bootstrap script. The value could be 'prod' (production), 'dev' (development), 'test', 'staging', etc.
 */
defined('LEAPS_ENV') or define('LEAPS_ENV', 'prod');

/**
 * Whether the the application is running in production environment
 */
defined('LEAPS_ENV_PROD') or define('LEAPS_ENV_PROD', LEAPS_ENV === 'prod');

/**
 * Whether the the application is running in development environment
 */
defined('LEAPS_ENV_DEV') or define('LEAPS_ENV_DEV', LEAPS_ENV === 'dev');

/**
 * Whether the the application is running in testing environment
 */
defined('LEAPS_ENV_TEST') or define('LEAPS_ENV_TEST', LEAPS_ENV === 'test');

/**
 * This constant defines whether error handling should be enabled.
 * Defaults to true.
 */
defined ( 'LEAPS_ENABLE_ERROR_HANDLER' ) or define ( 'LEAPS_ENABLE_ERROR_HANDLER', true );

/**
 * 加载Leaps核心
 */
require (__DIR__ . '/src/Leaps/Kernel.php');

/**
 * 重写Leaps
 * @author Xutongle
 *
 */
class Leaps extends \Leaps\Kernel
{
}

/**
 * 初始化自动加载
 */
spl_autoload_register ( [ 'Leaps','autoload' ], true, true );
