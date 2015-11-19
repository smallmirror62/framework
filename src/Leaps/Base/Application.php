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
 * Application 所有应用程序基类
 *
 * @property \Leaps\Web\AssetManager $assetManager The asset manager application component. This property is
 *           read-only.
 * @property \Leaps\Rbac\ManagerInterface $authManager The auth manager application component. Null is returned
 *           if auth manager is not configured. This property is read-only.
 * @property string $basePath The root directory of the application.
 * @property \Leaps\Cache\Cache $cache The cache application component. Null if the component is not enabled.
 *           This property is read-only.
 * @property \Leaps\Db\Connection $db The database connection. This property is read-only.
 * @property \Leaps\Web\ErrorHandler|\Leaps\console\ErrorHandler $errorHandler The error handler application
 *           component. This property is read-only.
 * @property \Leaps\I18n\Formatter $formatter The formatter application component. This property is read-only.
 * @property \Leaps\I18n\I18N $i18n The internationalization application component. This property is read-only.
 * @property \Leaps\Log\Dispatcher $log The log dispatcher application component. This property is read-only.
 * @property \Leaps\Mail\MailerInterface $mailer The mailer application component. This property is read-only.
 * @property \Leaps\Web\Request|\Leaps\Console\Request $request The request component. This property is read-only.
 * @property \Leaps\Web\Response|\Leaps\Console\Response $response The response component. This property is
 *           read-only.
 * @property string $runtimePath The directory that stores runtime files. Defaults to the "runtime"
 *           subdirectory under [[basePath]].
 * @property \Leaps\Base\Security $security The security application component. This property is read-only.
 * @property string $timeZone The time zone used by this application.
 * @property string $uniqueId The unique ID of the module. This property is read-only.
 * @property \Leaps\Web\UrlManager $urlManager The URL manager for this application. This property is read-only.
 * @property string $vendorPath The directory that stores vendor files. Defaults to "vendor" directory under
 *           [[basePath]].
 * @property View|\Leaps\Web\View $view The view application component that is used to render various view
 *           files. This property is read-only.
 *          
 */
abstract class Application extends Module
{
	/**
	 * @event Event an event raised before the application starts to handle a request.
	 */
	const EVENT_BEFORE_REQUEST = 'beforeRequest';
	/**
	 * @event Event an event raised after the application successfully handles a request (before the response is sent out).
	 */
	const EVENT_AFTER_REQUEST = 'afterRequest';
	/**
	 * Application state used by [[state]]: application just started.
	 */
	const STATE_BEGIN = 0;
	/**
	 * Application state used by [[state]]: application is initializing.
	 */
	const STATE_INIT = 1;
	/**
	 * Application state used by [[state]]: application is triggering [[EVENT_BEFORE_REQUEST]].
	 */
	const STATE_BEFORE_REQUEST = 2;
	/**
	 * Application state used by [[state]]: application is handling the request.
	 */
	const STATE_HANDLING_REQUEST = 3;
	/**
	 * Application state used by [[state]]: application is triggering [[EVENT_AFTER_REQUEST]]..
	 */
	const STATE_AFTER_REQUEST = 4;
	/**
	 * Application state used by [[state]]: application is about to send response.
	 */
	const STATE_SENDING_RESPONSE = 5;
	/**
	 * Application state used by [[state]]: application has ended.
	 */
	const STATE_END = 6;
	
	/**
	 *
	 * @var string the namespace that controller classes are located in.
	 *      This namespace will be used to load controller classes by prepending it to the controller class name.
	 *      The default namespace is `app\controllers`.
	 *     
	 *      Please refer to the [guide about class autoloading](guide:concept-autoloading.md) for more details.
	 */
	public $controllerNamespace = 'App\\Controller';
	/**
	 *
	 * @var string the application name.
	 */
	public $name = 'My Application';
	/**
	 *
	 * @var string the version of this application.
	 */
	public $version = '1.0';
	/**
	 *
	 * @var string the charset currently used for the application.
	 */
	public $charset = 'UTF-8';
	/**
	 *
	 * @var string the language that is meant to be used for end users. It is recommended that you
	 *      use [IETF language tags](http://en.wikipedia.org/wiki/IETF_language_tag). For example, `en` stands
	 *      for English, while `en-US` stands for English (United States).
	 * @see sourceLanguage
	 */
	public $language = 'en-US';
	/**
	 *
	 * @var string the language that the application is written in. This mainly refers to
	 *      the language that the messages and view files are written in.
	 * @see language
	 */
	public $sourceLanguage = 'en-US';
	
	/**
	 * 当前活动的控制器实例
	 *
	 * @var Controller
	 */
	public $controller;
	/**
	 *
	 * @var string|boolean the layout that should be applied for views in this application. Defaults to 'main'.
	 *      If this is false, layout will be disabled.
	 */
	public $layout = 'main';
	/**
	 *
	 * @var string the requested route
	 */
	public $requestedRoute;
	/**
	 *
	 * @var Action the requested Action. If null, it means the request cannot be resolved into an action.
	 */
	public $requestedAction;
	/**
	 *
	 * @var array the parameters supplied to the requested action.
	 */
	public $requestedParams;
	/**
	 *
	 * @var array list of installed Leaps extensions. Each array element represents a single extension
	 *      with the following structure:
	 *     
	 *      ~~~
	 *      [
	 *      'name' => 'extension name',
	 *      'version' => 'version number',
	 *      'bootstrap' => 'BootstrapClassName', // optional, may also be a configuration array
	 *      'alias' => [
	 *      '@alias1' => 'to/path1',
	 *      '@alias2' => 'to/path2',
	 *      ],
	 *      ]
	 *      ~~~
	 *     
	 *      The "bootstrap" class listed above will be instantiated during the application
	 *      [[bootstrap()|bootstrapping process]]. If the class implements [[BootstrapInterface]],
	 *      its [[BootstrapInterface::bootstrap()|bootstrap()]] method will be also be called.
	 *     
	 *      If not set explicitly in the application config, this property will be populated with the contents of
	 *      `@vendor/leaps/extensions.php`.
	 */
	public $extensions;
	/**
	 *
	 * @var array list of components that should be run during the application [[bootstrap()|bootstrapping process]].
	 *     
	 *      Each component may be specified in one of the following formats:
	 *     
	 *      - an application component ID as specified via [[components]].
	 *      - a module ID as specified via [[modules]].
	 *      - a class name.
	 *      - a configuration array.
	 *     
	 *      During the bootstrapping process, each component will be instantiated. If the component class
	 *      implements [[BootstrapInterface]], its [[BootstrapInterface::bootstrap()|bootstrap()]] method
	 *      will be also be called.
	 */
	public $bootstrap = [ ];
	/**
	 *
	 * @var integer the current application state during a request handling life cycle.
	 *      This property is managed by the application. Do not modify this property.
	 */
	public $state;
	/**
	 *
	 * @var array list of loaded modules indexed by their class names.
	 */
	public $loadedModules = [ ];
	
	/**
	 * Constructor.
	 *
	 * @param array $config name-value pairs that will be used to initialize the object properties.
	 *        Note that the configuration must contain both [[id]] and [[basePath]].
	 * @throws InvalidConfigException if either [[id]] or [[basePath]] configuration is missing.
	 */
	public function __construct($config = [])
	{
		Leaps::$app = $this;
		$this->setInstance ( $this );
		
		$this->state = self::STATE_BEGIN;
		
		$this->preInit ( $config );
		
		$this->registerErrorHandler ( $config );
		
		Service::__construct ( $config );
	}
	
	/**
	 * Pre-initializes the application.
	 * This method is called at the beginning of the application constructor.
	 * It initializes several important application properties.
	 * If you override this method, please make sure you call the parent implementation.
	 *
	 * @param array $config the application configuration
	 * @throws InvalidConfigException if either [[id]] or [[basePath]] configuration is missing.
	 */
	public function preInit(&$config)
	{
		if (! isset ( $config ['id'] )) {
			throw new InvalidConfigException ( 'The "id" configuration for the Application is required.' );
		}
		if (isset ( $config ['basePath'] )) {
			$this->setBasePath ( $config ['basePath'] );
			unset ( $config ['basePath'] );
		} else {
			throw new InvalidConfigException ( 'The "basePath" configuration for the Application is required.' );
		}
		
		if (isset ( $config ['vendorPath'] )) {
			$this->setVendorPath ( $config ['vendorPath'] );
			unset ( $config ['vendorPath'] );
		} else {
			// set "@vendor"
			$this->getVendorPath ();
		}
		if (isset ( $config ['runtimePath'] )) {
			$this->setRuntimePath ( $config ['runtimePath'] );
			unset ( $config ['runtimePath'] );
		} else {
			// set "@runtime"
			$this->getRuntimePath ();
		}
		
		if (isset ( $config ['timeZone'] )) {
			$this->setTimeZone ( $config ['timeZone'] );
			unset ( $config ['timeZone'] );
		} elseif (! ini_get ( 'date.timezone' )) {
			$this->setTimeZone ( 'UTC' );
		}
		
		// 合并核心服务和自定义服务
		foreach ( $this->coreServices () as $id => $service ) {
			if (! isset ( $config ['services'] [$id] )) {
				$config ['services'] [$id] = $service;
			} elseif (is_array ( $config ['services'] [$id] ) && ! isset ( $config ['services'] [$id] ['className'] )) {
				$config ['services'] [$id] ['className'] = $service ['className'];
			}
		}
	}
	
	/**
	 * @inheritdoc
	 */
	public function init()
	{
		$this->state = self::STATE_INIT;
		$this->bootstrap ();
	}
	
	/**
	 * Initializes extensions and executes bootstrap components.
	 * This method is called by [[init()]] after the application has been fully configured.
	 * If you override this method, make sure you also call the parent implementation.
	 */
	protected function bootstrap()
	{
		if ($this->extensions === null) {
			$file = Leaps::getAlias ( '@Vendor/leaps/extensions.php' );
			$this->extensions = is_file ( $file ) ? include ($file) : [ ];
		}
		foreach ( $this->extensions as $extension ) {
			if (! empty ( $extension ['alias'] )) {
				foreach ( $extension ['alias'] as $name => $path ) {
					Leaps::setAlias ( $name, $path );
				}
			}
			if (isset ( $extension ['bootstrap'] )) {
				$service = Leaps::createObject ( $extension ['bootstrap'] );
				if ($service instanceof BootstrapInterface) {
					Leaps::trace ( "Bootstrap with " . get_class ( $service ) . '::bootstrap()', __METHOD__ );
					$service->bootstrap ( $this );
				} else {
					Leaps::trace ( "Bootstrap with " . get_class ( $service ), __METHOD__ );
				}
			}
		}
		
		foreach ( $this->bootstrap as $class ) {
			$service = null;
			if (is_string ( $class )) {
				if ($this->has ( $class )) {
					$service = $this->get ( $class );
				} elseif ($this->hasModule ( $class )) {
					$service = $this->getModule ( $class );
				} elseif (strpos ( $class, '\\' ) === false) {
					throw new InvalidConfigException ( "Unknown bootstrapping component ID: $class" );
				}
			}
			if (! isset ( $service )) {
				$service = Leaps::createObject ( $class );
			}
			
			if ($service instanceof BootstrapInterface) {
				Leaps::trace ( "Bootstrap with " . get_class ( $service ) . '::bootstrap()', __METHOD__ );
				$service->bootstrap ( $this );
			} else {
				Leaps::trace ( "Bootstrap with " . get_class ( $service ), __METHOD__ );
			}
		}
	}
	
	/**
	 * Registers the errorHandler service as a PHP error handler.
	 *
	 * @param array $config application config
	 */
	protected function registerErrorHandler(&$config)
	{
		if (LEAPS_ENABLE_ERROR_HANDLER) {
			if (! isset ( $config ['services'] ['errorHandler'] ['className'] )) {
				echo "Error: no errorHandler service is configured.\n";
				exit ( 1 );
			}
			$this->set ( 'errorHandler', $config ['services'] ['errorHandler'] );
			unset ( $config ['services'] ['errorHandler'] );
			$this->getErrorHandler ()->register ();
		}
	}
	
	/**
	 * Returns an ID that uniquely identifies this module among all modules within the current application.
	 * Since this is an application instance, it will always return an empty string.
	 *
	 * @return string the unique ID of the module.
	 */
	public function getUniqueId()
	{
		return '';
	}
	
	/**
	 * Sets the root directory of the application and the @app alias.
	 * This method can only be invoked at the beginning of the constructor.
	 *
	 * @param string $path the root directory of the application.
	 * @property string the root directory of the application.
	 * @throws InvalidParamException if the directory does not exist.
	 */
	public function setBasePath($path)
	{
		parent::setBasePath ( $path );
		Leaps::setAlias ( '@app', $this->getBasePath () );
	}
	
	/**
	 * Runs the application.
	 * This is the main entrance of an application.
	 *
	 * @return integer the exit status (0 means normal, non-zero values mean abnormal)
	 */
	public function run()
	{
		try {
			
			$this->state = self::STATE_BEFORE_REQUEST;
			$this->trigger ( self::EVENT_BEFORE_REQUEST );
			
			$this->state = self::STATE_HANDLING_REQUEST;
			$response = $this->handleRequest ( $this->getRequest () );
			
			$this->state = self::STATE_AFTER_REQUEST;
			$this->trigger ( self::EVENT_AFTER_REQUEST );
			
			$this->state = self::STATE_SENDING_RESPONSE;
			$response->send ();
			
			$this->state = self::STATE_END;
			
			return $response->exitStatus;
		} catch ( ExitException $e ) {
			
			$this->end ( $e->statusCode, isset ( $response ) ? $response : null );
			return $e->statusCode;
		}
	}
	
	/**
	 * Handles the specified request.
	 *
	 * This method should return an instance of [[Response]] or its child class
	 * which represents the handling result of the request.
	 *
	 * @param Request $request the request to be handled
	 * @return Response the resulting response
	 */
	abstract public function handleRequest($request);
	private $_runtimePath;
	
	/**
	 * Returns the directory that stores runtime files.
	 *
	 * @return string the directory that stores runtime files.
	 *         Defaults to the "runtime" subdirectory under [[basePath]].
	 */
	public function getRuntimePath()
	{
		if ($this->_runtimePath === null) {
			$this->setRuntimePath ( $this->getBasePath () . DIRECTORY_SEPARATOR . 'Runtime' );
		}
		
		return $this->_runtimePath;
	}
	
	/**
	 * Sets the directory that stores runtime files.
	 *
	 * @param string $path the directory that stores runtime files.
	 */
	public function setRuntimePath($path)
	{
		$this->_runtimePath = Leaps::getAlias ( $path );
		Leaps::setAlias ( '@Runtime', $this->_runtimePath );
	}
	private $_vendorPath;
	
	/**
	 * Returns the directory that stores vendor files.
	 *
	 * @return string the directory that stores vendor files.
	 *         Defaults to "vendor" directory under [[basePath]].
	 */
	public function getVendorPath()
	{
		if ($this->_vendorPath === null) {
			$this->setVendorPath ( $this->getBasePath () . DIRECTORY_SEPARATOR . 'Vendor' );
		}
		
		return $this->_vendorPath;
	}
	
	/**
	 * Sets the directory that stores vendor files.
	 *
	 * @param string $path the directory that stores vendor files.
	 */
	public function setVendorPath($path)
	{
		$this->_vendorPath = Leaps::getAlias ( $path );
		Leaps::setAlias ( '@Vendor', $this->_vendorPath );
		Leaps::setAlias ( '@Bower', $this->_vendorPath . DIRECTORY_SEPARATOR . 'bower' );
		Leaps::setAlias ( '@Npm', $this->_vendorPath . DIRECTORY_SEPARATOR . 'npm' );
	}
	
	/**
	 * Returns the time zone used by this application.
	 * This is a simple wrapper of PHP function date_default_timezone_get().
	 * If time zone is not configured in php.ini or application config,
	 * it will be set to UTC by default.
	 *
	 * @return string the time zone used by this application.
	 * @see http://php.net/manual/en/function.date-default-timezone-get.php
	 */
	public function getTimeZone()
	{
		return date_default_timezone_get ();
	}
	
	/**
	 * Sets the time zone used by this application.
	 * This is a simple wrapper of PHP function date_default_timezone_set().
	 * Refer to the [php manual](http://www.php.net/manual/en/timezones.php) for available timezones.
	 *
	 * @param string $value the time zone used by this application.
	 * @see http://php.net/manual/en/function.date-default-timezone-set.php
	 */
	public function setTimeZone($value)
	{
		date_default_timezone_set ( $value );
	}
	
	/**
	 * Returns the database connection component.
	 *
	 * @return \Leaps\Db\Connection the database connection.
	 */
	public function getDb()
	{
		return $this->get ( 'db' );
	}
	
	/**
	 * Returns the log dispatcher component.
	 *
	 * @return \Leaps\Log\Dispatcher the log dispatcher application component.
	 */
	public function getLog()
	{
		return $this->get ( 'log' );
	}
	
	/**
	 * Returns the error handler component.
	 *
	 * @return \Leaps\Web\ErrorHandler|\Leaps\Console\ErrorHandler the error handler application component.
	 */
	public function getErrorHandler()
	{
		return $this->get ( 'errorHandler' );
	}
	
	/**
	 * Returns the cache component.
	 *
	 * @return \Leaps\cache\Cache the cache application component. Null if the component is not enabled.
	 */
	public function getCache()
	{
		return $this->get ( 'cache', false );
	}
	
	/**
	 * Returns the formatter component.
	 *
	 * @return \Leaps\I18n\Formatter the formatter application component.
	 */
	public function getFormatter()
	{
		return $this->get ( 'formatter' );
	}
	
	/**
	 * Returns the request component.
	 *
	 * @return \Leaps\Web\Request|\Leaps\Console\Request the request component.
	 */
	public function getRequest()
	{
		return $this->get ( 'request' );
	}
	
	/**
	 * Returns the response component.
	 *
	 * @return \Leaps\Web\Response|\Leaps\Console\Response the response component.
	 */
	public function getResponse()
	{
		return $this->get ( 'response' );
	}
	
	/**
	 * Returns the view object.
	 *
	 * @return View|\Leaps\Web\View the view application component that is used to render various view files.
	 */
	public function getView()
	{
		return $this->get ( 'view' );
	}
	
	/**
	 * Returns the URL manager for this application.
	 *
	 * @return \Leaps\Web\UrlManager the URL manager for this application.
	 */
	public function getUrlManager()
	{
		return $this->get ( 'urlManager' );
	}
	
	/**
	 * Returns the internationalization (i18n) component
	 *
	 * @return \Leaps\I18n\I18N the internationalization application component.
	 */
	public function getI18n()
	{
		return $this->get ( 'i18n' );
	}
	
	/**
	 * Returns the mailer component.
	 *
	 * @return \Leaps\Mail\MailerInterface the mailer application component.
	 */
	public function getMailer()
	{
		return $this->get ( 'mailer' );
	}
	
	/**
	 * Returns the auth manager for this application.
	 *
	 * @return \Leaps\Rbac\ManagerInterface the auth manager application component.
	 *         Null is returned if auth manager is not configured.
	 */
	public function getAuthManager()
	{
		return $this->get ( 'authManager', false );
	}
	
	/**
	 * Returns the asset manager.
	 *
	 * @return \Leaps\Web\AssetManager the asset manager application component.
	 */
	public function getAssetManager()
	{
		return $this->get ( 'assetManager' );
	}
	
	/**
	 * Returns the security component.
	 *
	 * @return \Leaps\Base\Security the security application component.
	 */
	public function getSecurity()
	{
		return $this->get ( 'security' );
	}
	
	/**
	 * Returns the configuration of core application components.
	 *
	 * @see set()
	 */
	public function coreServices()
	{
		return [ 
			'log' => [ 
				'className' => 'Leaps\Log\Dispatcher' 
			],
			'view' => [ 
				'className' => 'Leaps\Web\View' 
			],
			'formatter' => [ 
				'className' => 'Leaps\I18n\Formatter' 
			],
			'i18n' => [ 
				'className' => 'Leaps\I18n\I18N' 
			],
			'mailer' => [ 
				'className' => 'Leaps\Swiftmailer\Mailer' 
			],
			'urlManager' => [ 
				'className' => 'Leaps\Web\UrlManager' 
			],
			'assetManager' => [ 
				'className' => 'Leaps\Web\AssetManager' 
			],
			'security' => [ 
				'className' => 'Leaps\Base\Security' 
			] 
		];
	}
	
	/**
	 * 终止应用程序
	 * This method replaces the `exit()` function by ensuring the application life cycle is completed
	 * before terminating the application.
	 *
	 * @param integer $status the exit status (value 0 means normal exit while other values mean abnormal exit).
	 * @param Response $response the response to be sent. If not set, the default application [[response]] component will be used.
	 * @throws ExitException if the application is in testing mode
	 */
	public function end($status = 0, $response = null)
	{
		if ($this->state === self::STATE_BEFORE_REQUEST || $this->state === self::STATE_HANDLING_REQUEST) {
			$this->state = self::STATE_AFTER_REQUEST;
			$this->trigger ( self::EVENT_AFTER_REQUEST );
		}
		
		if ($this->state !== self::STATE_SENDING_RESPONSE && $this->state !== self::STATE_END) {
			$this->state = self::STATE_END;
			$response = $response ?  : $this->getResponse ();
			$response->send ();
		}
		
		if (LEAPS_ENV_TEST) {
			throw new ExitException ( $status );
		} else {
			exit ( $status );
		}
	}
}
