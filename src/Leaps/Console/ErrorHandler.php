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
namespace Leaps\Console;

use Leaps;
use Leaps\Helper\Console;
use Leaps\Base\ErrorException;
use Leaps\Base\UserException;

/**
 * ErrorHandler handles uncaught PHP errors and exceptions.
 *
 * ErrorHandler is configured as an application component in [[\Leaps\base\Application]] by default.
 * You can access that instance via `Leaps::$app->errorHandler`.
 */
class ErrorHandler extends \Leaps\Base\ErrorHandler
{
	/**
	 * Renders an exception using ansi format for console output.
	 *
	 * @param \Exception $exception the exception to be rendered.
	 */
	protected function renderException($exception)
	{
		if ($exception instanceof Exception && ($exception instanceof UserException || ! LEAPS_DEBUG)) {
			$message = $this->formatMessage ( $exception->getName () . ': ' ) . $exception->getMessage ();
		} elseif (LEAPS_DEBUG) {
			if ($exception instanceof Exception) {
				$message = $this->formatMessage ( "Exception ({$exception->getName()})" );
			} elseif ($exception instanceof ErrorException) {
				$message = $this->formatMessage ( $exception->getName () );
			} else {
				$message = $this->formatMessage ( 'Exception' );
			}
			$message .= $this->formatMessage ( " '" . get_class ( $exception ) . "'", [ 
				Console::BOLD,
				Console::FG_BLUE 
			] ) . " with message " . $this->formatMessage ( "'{$exception->getMessage()}'", [ 
				Console::BOLD 
			] ) . // . "\n"
"\n\nin " . dirname ( $exception->getFile () ) . DIRECTORY_SEPARATOR . $this->formatMessage ( basename ( $exception->getFile () ), [ 
				Console::BOLD 
			] ) . ':' . $this->formatMessage ( $exception->getLine (), [ 
				Console::BOLD,
				Console::FG_YELLOW 
			] ) . "\n";
			if ($exception instanceof \Leaps\Db\Exception && ! empty ( $exception->errorInfo )) {
				$message .= "\n" . $this->formatMessage ( "Error Info:\n", [ 
					Console::BOLD 
				] ) . print_r ( $exception->errorInfo, true );
			}
			$message .= "\n" . $this->formatMessage ( "Stack trace:\n", [ 
				Console::BOLD 
			] ) . $exception->getTraceAsString ();
		} else {
			$message = $this->formatMessage ( 'Error: ' ) . $exception->getMessage ();
		}
		
		if (PHP_SAPI === 'cli') {
			Console::stderr ( $message . "\n" );
		} else {
			echo $message . "\n";
		}
	}
	
	/**
	 * Colorizes a message for console output.
	 *
	 * @param string $message the message to colorize.
	 * @param array $format the message format.
	 * @return string the colorized message.
	 * @see Console::ansiFormat() for details on how to specify the message format.
	 */
	protected function formatMessage($message, $format = [Console::FG_RED, Console::BOLD])
	{
		$stream = (PHP_SAPI === 'cli') ? \STDERR : \STDOUT;
		// try controller first to allow check for --color switch
		if (Leaps::$app->controller instanceof \Leaps\Console\Controller && Leaps::$app->controller->isColorEnabled ( $stream ) || Leaps::$app instanceof \Leaps\Console\Application && Console::streamSupportsAnsiColors ( $stream )) {
			$message = Console::ansiFormat ( $message, $format );
		}
		return $message;
	}
}