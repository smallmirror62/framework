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
namespace Leaps\Log;

use Leaps;
use Leaps\Di\Injectable;
use Leaps\Core\ErrorHandler;

/**
 * 调度管理
 */
class Dispatcher extends Injectable
{
	/**
	 * 日志处理器实例
	 * @var array|Target[]
	 */
	public $targets = [ ];

	/**
	 * 日志
	 * @var Logger
	 */
	private $_logger;

	/**
	 * @inheritdoc
	 */
	public function __construct($config = [])
	{
		// ensure logger gets set before any other config option
		if (isset ( $config ['logger'] )) {
			$this->setLogger ( $config ['logger'] );
			unset ( $config ['logger'] );
		}
		// connect logger and dispatcher
		$this->getLogger ();
		parent::__construct ( $config );
	}

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init ();
		foreach ( $this->targets as $name => $target ) {
			if (! $target instanceof Target) {
				$this->targets [$name] = Leaps::createObject ( $target );
			}
		}
	}

	/**
	 * 获取日志连接
	 * If not set, [[\Leaps\Kernel::getLogger()]] will be used.
	 *
	 * @property Logger the logger. If not set, [[\Leaps\Kernel::getLogger()]] will be used.
	 * @return Logger the logger.
	 */
	public function getLogger()
	{
		if ($this->_logger === null) {
			$this->setLogger ( Leaps::getLogger () );
		}
		return $this->_logger;
	}

	/**
	 * Sets the connected logger.
	 *
	 * @param Logger $value the logger.
	 */
	public function setLogger($value)
	{
		$this->_logger = $value;
		$this->_logger->dispatcher = $this;
	}

	/**
	 *
	 * @return integer how many application call stacks should be logged together with each message.
	 *         This method returns the value of [[Logger::traceLevel]]. Defaults to 0.
	 */
	public function getTraceLevel()
	{
		return $this->getLogger ()->traceLevel;
	}

	/**
	 *
	 * @param integer $value how many application call stacks should be logged together with each message.
	 *        This method will set the value of [[Logger::traceLevel]]. If the value is greater than 0,
	 *        at most that number of call stacks will be logged. Note that only application call stacks are counted.
	 *        Defaults to 0.
	 */
	public function setTraceLevel($value)
	{
		$this->getLogger ()->traceLevel = $value;
	}

	/**
	 *
	 * @return integer how many messages should be logged before they are sent to targets.
	 *         This method returns the value of [[Logger::flushInterval]].
	 */
	public function getFlushInterval()
	{
		return $this->getLogger ()->flushInterval;
	}

	/**
	 *
	 * @param integer $value how many messages should be logged before they are sent to targets.
	 *        This method will set the value of [[Logger::flushInterval]].
	 *        Defaults to 1000, meaning the [[Logger::flush()]] method will be invoked once every 1000 messages logged.
	 *        Set this property to be 0 if you don't want to flush messages until the application terminates.
	 *        This property mainly affects how much memory will be taken by the logged messages.
	 *        A smaller value means less memory, but will increase the execution time due to the overhead of [[Logger::flush()]].
	 */
	public function setFlushInterval($value)
	{
		$this->getLogger ()->flushInterval = $value;
	}

	/**
	 * 调度日志消息到目标存储
	 *
	 * @param array $messages 日志消息
	 * @param boolean $final 此方法是否在当前应用程序结束时调用
	 */
	public function dispatch($messages, $final)
	{
		$targetErrors = [ ];
		foreach ( $this->targets as $target ) {
			if ($target->enabled) {
				try {
					$target->collect ( $messages, $final );
				} catch ( \Exception $e ) {
					$target->enabled = false;
					$targetErrors [] = [ 'Unable to send log via ' . get_class ( $target ) . ': ' . ErrorHandler::convertExceptionToString ( $e ),Logger::LEVEL_WARNING,__METHOD__,microtime ( true ),[ ] ];
				}
			}
		}
		if (! empty ( $targetErrors )) {
			$this->dispatch ( $targetErrors, true );
		}
	}
}
