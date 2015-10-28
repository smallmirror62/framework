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
namespace Leaps\Core;

use Leaps;
use Leaps\Di\Injectable;
use Leaps\Web\HttpException;
use Leaps\Helper\VarDumper;


abstract class ErrorHandler extends Injectable
{

	/**
	 *
	 * @var boolean whether to discard any existing page output before error display. Defaults to true.
	 */
	public $discardExistingOutput = true;

	/**
	 *
	 * @var integer the size of the reserved memory. A portion of memory is pre-allocated so that
	 *      when an out-of-memory issue occurs, the error handler is able to handle the error with
	 *      the help of this reserved memory. If you set this value to be 0, no memory will be reserved.
	 *      Defaults to 256KB.
	 */
	public $memoryReserveSize = 262144;

	/**
	 *
	 * @var \Exception the exception that is being handled currently.
	 */
	public $exception;

	/**
	 *
	 * @var string Used to reserve memory for fatal error handler.
	 */
	private $_memoryReserve;

	/**
	 * 注册错误处理
	 */
	public function register()
	{
		ini_set ( 'display_errors', true );
		set_exception_handler ( [ $this,'handleException' ] );
		set_error_handler ( [ $this,'handleError' ] );
		if ($this->memoryReserveSize > 0) {
			$this->_memoryReserve = str_repeat ( 'x', $this->memoryReserveSize );
		}
		register_shutdown_function ( [ $this,'handleFatalError' ] );
	}

	/**
	 * Unregisters this error handler by restoring the PHP error and exception handlers.
	 */
	public function unregister()
	{
		restore_error_handler ();
		restore_exception_handler ();
	}

	/**
	 * 处理未捕获的PHP异常。
	 *
	 * 这个方法是一个PHP异常处理程序的实现。
	 *
	 * @param \Exception $exception the exception that is not caught
	 */
	public function handleException($exception)
	{
		if ($exception instanceof ExitException) {
			return;
		}
		$this->exception = $exception;
		// disable error capturing to avoid recursive errors while handling exceptions
		$this->unregister ();
		// set preventive HTTP status code to 500 in case error handling somehow fails and headers are sent
		// HTTP exceptions will override this value in renderException()
		if (PHP_SAPI !== 'cli') {
			http_response_code ( 500 );
		}
		try {
			$this->logException ( $exception );
			if ($this->discardExistingOutput) {
				$this->clearOutput ();
			}
			$this->renderException ( $exception );
			if (!LEAPS_ENV_TEST) {
				exit ( 1 );
			}
		} catch ( \Exception $e ) {
			// an other exception could be thrown while displaying the exception
			$msg = "An Error occurred while handling another error:\n";
			$msg .= ( string ) $e;
			$msg .= "\nPrevious exception:\n";
			$msg .= ( string ) $exception;
			if (LEAPS_DEBUG) {
				if (PHP_SAPI === 'cli') {
					echo $msg . "\n";
				} else {
					echo '<pre>' . htmlspecialchars ( $msg, ENT_QUOTES, Leaps::app ()->charset ) . '</pre>';
				}
			} else {
				echo 'An internal server error occurred.';
			}
			$msg .= "\n\$_SERVER = " . VarDumper::export ( $_SERVER );
			error_log ( $msg );
			exit ( 1 );
		}

		$this->exception = null;
	}

	/**
	 * Handles PHP execution errors such as warnings and notices.
	 *
	 * This method is used as a PHP error handler. It will simply raise an [[ErrorException]].
	 *
	 * @param integer $code the level of the error raised.
	 * @param string $message the error message.
	 * @param string $file the filename that the error was raised in.
	 * @param integer $line the line number the error was raised at.
	 * @return boolean whether the normal error handler continues.
	 *
	 * @throws ErrorException
	 */
	public function handleError($code, $message, $file, $line)
	{
		if (error_reporting () & $code) {
			// load ErrorException manually here because autoloading them will not work
			// when error occurs while autoloading a class
			if (! class_exists ( 'Leaps\\Core\\ErrorException', false )) {
				require_once (__DIR__ . '/ErrorException.php');
			}
			$exception = new ErrorException ( $message, $code, $code, $file, $line );
			// in case error appeared in __toString method we can't throw any exception
			$trace = debug_backtrace ( DEBUG_BACKTRACE_IGNORE_ARGS );
			array_shift ( $trace );
			foreach ( $trace as $frame ) {
				if ($frame ['function'] == '__toString') {
					$this->handleException ( $exception );
					exit ( 1 );
				}
			}
			throw $exception;
		}
		return false;
	}

	/**
	 * 处理PHP致命错误
	 */
	public function handleFatalError()
	{
		unset ( $this->_memoryReserve );
		// load ErrorException manually here because autoloading them will not work
		// when error occurs while autoloading a class
		if (! class_exists ( 'Leaps\\Core\\ErrorException', false )) {
			require_once (__DIR__ . '/ErrorException.php');
		}
		$error = error_get_last ();
		if (ErrorException::isFatalError ( $error )) {
			$exception = new ErrorException ( $error ['message'], $error ['type'], $error ['type'], $error ['file'], $error ['line'] );
			$this->exception = $exception;
			$this->logException ( $exception );
			if ($this->discardExistingOutput) {
				$this->clearOutput ();
			}
			$this->renderException ( $exception );
			// need to explicitly flush logs because exit() next will terminate the app immediately
			Leaps::getLogger()->flush(true);
			exit ( 1 );
		}
	}

	/**
	 * 渲染异常
	 *
	 * @param \Exception $exception the exception to be rendered.
	 */
	abstract protected function renderException($exception);

	/**
	 * 记录异常日志
	 *
	 * @param \Exception $exception the exception to be logged
	 * @since 2.0.3 this method is now public.
	 */
	public function logException($exception)
	{
		$category = get_class ( $exception );
		if ($exception instanceof HttpException) {
			$category = 'Leaps\Web\\HttpException:' . $exception->statusCode;
		} elseif ($exception instanceof \ErrorException) {
			$category .= ':' . $exception->getSeverity ();
		}
		Leaps::error ( ( string ) $exception, $category );
	}

	/**
	 * 删除所有输出之前调用此方法。
	 */
	public function clearOutput()
	{
		// the following manual level counting is to deal with zlib.output_compression set to On
		for($level = ob_get_level (); $level > 0; -- $level) {
			if (! @ob_end_clean ()) {
				ob_clean ();
			}
		}
	}

	/**
	 * 将异常转换为PHP的错误。
	 *
	 * This method can be used to convert exceptions inside of methods like `__toString()`
	 * to PHP errors because exceptions cannot be thrown inside of them.
	 *
	 * @param \Exception $exception the exception to convert to a PHP error.
	 */
	public static function convertExceptionToError($exception)
	{
		trigger_error ( static::convertExceptionToString ( $exception ), E_USER_ERROR );
	}

	/**
	 * 把一个异常转换为一个简单的字符串。
	 *
	 * @param \Exception $exception the exception being converted
	 * @return string the string representation of the exception.
	 */
	public static function convertExceptionToString($exception)
	{
		if ($exception instanceof Exception && ($exception instanceof UserException || !LEAPS_DEBUG)) {
			$message = "{$exception->getName()}: {$exception->getMessage()}";
		} elseif (LEAPS_DEBUG) {
			if ($exception instanceof Exception) {
				$message = "Exception ({$exception->getName()})";
			} elseif ($exception instanceof ErrorException) {
				$message = "{$exception->getName()}";
			} else {
				$message = 'Exception';
			}
			$message .= " '" . get_class ( $exception ) . "' with message '{$exception->getMessage()}' \n\nin " . $exception->getFile () . ':' . $exception->getLine () . "\n\n" . "Stack trace:\n" . $exception->getTraceAsString ();
		} else {
			$message = 'Error: ' . $exception->getMessage ();
		}
		return $message;
	}
}