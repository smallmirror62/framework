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

use Leaps\Di\Injectable;

class Logger extends Injectable
{
	/**
	 * Error 级别
	 */
	const LEVEL_ERROR = 0x01;

	/**
	 * Warning 级别
	 */
	const LEVEL_WARNING = 0x02;

	/**
	 * Informational 级别
	 */
	const LEVEL_INFO = 0x04;

	/**
	 * Tracing 级别
	 */
	const LEVEL_TRACE = 0x08;

	/**
	 * Profiling 级别
	 */
	const LEVEL_PROFILE = 0x40;

	/**
	 * Profiling 级别
	 */
	const LEVEL_PROFILE_BEGIN = 0x50;

	/**
	 * Profiling 级别
	 */
	const LEVEL_PROFILE_END = 0x60;

	/**
	 * 日志消息
	 * @var array 日志消息
	 *      每个日志消息都是以下结构:
	 *
	 *      ~~~
	 *      [
	 *      [0] => message (mixed, can be a string or some complex data, such as an exception object)
	 *      [1] => level (integer)
	 *      [2] => category (string)
	 *      [3] => timestamp (float, obtained by microtime(true))
	 *      [4] => traces (array, debug backtrace, contains the application code call stacks)
	 *      ]
	 *      ~~~
	 */
	public $messages = [ ];

	/**
	 * 在刷新内存和发送到目标之前，应记录多少信息。
	 * @var integer how many messages should be logged before they are flushed from memory and sent to targets.
	 */
	public $flushInterval = 1000;

	/**
	 * 每一条消息应记录多少调用堆栈信息（文件名和行数）。
	 * @var integer how much call stack information (file name and line number) should be logged for each message.
	 */
	public $traceLevel = 0;

	/**
	 * 消息调度器
	 * @var Dispatcher
	 */
	public $dispatcher;

	/**
	 * 初始化
	 */
	public function init()
	{
		register_shutdown_function ( function () {
			$this->flush ();
			register_shutdown_function ( [ $this,'flush' ], true );
		} );
	}

	/**
	 * 记录指定类型和类别的消息。
	 *
	 * @param string|array $message 消息内容
	 * @param integer $level 消息类别
	 * @param string $category 消息分类
	 */
	public function log($message, $level, $category = 'application')
	{
		$time = microtime ( true );
		$traces = [ ];
		if ($this->traceLevel > 0) {
			$count = 0;
			$ts = debug_backtrace ( DEBUG_BACKTRACE_IGNORE_ARGS );
			array_pop ( $ts ); // remove the last trace since it would be the entry script, not very useful
			foreach ( $ts as $trace ) {
				if (isset ( $trace ['file'], $trace ['line'] ) && strpos ( $trace ['file'], YII2_PATH ) !== 0) {
					unset ( $trace ['object'], $trace ['args'] );
					$traces [] = $trace;
					if (++ $count >= $this->traceLevel) {
						break;
					}
				}
			}
		}
		$this->messages [] = [ $message,$level,$category,$time,$traces ];
		if ($this->flushInterval > 0 && count ( $this->messages ) >= $this->flushInterval) {
			$this->flush ();
		}
	}

	/**
	 * 将日志发送给记录器
	 *
	 * @param boolean $final whether this is a final call during a request.
	 */
	public function flush($final = false)
	{
		$messages = $this->messages;
		// https://github.com/yiisoft/yii2/issues/5619
		// new messages could be logged while the existing ones are being handled by targets
		$this->messages = [ ];
		if ($this->dispatcher instanceof Dispatcher) {
			$this->dispatcher->dispatch ( $messages, $final );
		}
	}

	/**
	 * 返回当前请求的总共时间
	 *
	 * @return float the total elapsed time in seconds for current request.
	 */
	public function getElapsedTime()
	{
		return microtime ( true ) - LEAPS_BEGIN_TIME;
	}

	/**
	 * 返回分析结果
	 *
	 * @param array $categories list of categories that you are interested in.
	 *        You can use an asterisk at the end of a category to do a prefix match.
	 *        For example, 'yii\db\*' will match categories starting with 'yii\db\',
	 *        such as 'yii\db\Connection'.
	 * @param array $excludeCategories list of categories that you want to exclude
	 * @return array the profiling results. Each element is an array consisting of these elements:
	 *         `info`, `category`, `timestamp`, `trace`, `level`, `duration`.
	 */
	public function getProfiling($categories = [], $excludeCategories = [])
	{
		$timings = $this->calculateTimings ( $this->messages );
		if (empty ( $categories ) && empty ( $excludeCategories )) {
			return $timings;
		}

		foreach ( $timings as $i => $timing ) {
			$matched = empty ( $categories );
			foreach ( $categories as $category ) {
				$prefix = rtrim ( $category, '*' );
				if (($timing ['category'] === $category || $prefix !== $category) && strpos ( $timing ['category'], $prefix ) === 0) {
					$matched = true;
					break;
				}
			}

			if ($matched) {
				foreach ( $excludeCategories as $category ) {
					$prefix = rtrim ( $category, '*' );
					foreach ( $timings as $i => $timing ) {
						if (($timing ['category'] === $category || $prefix !== $category) && strpos ( $timing ['category'], $prefix ) === 0) {
							$matched = false;
							break;
						}
					}
				}
			}

			if (! $matched) {
				unset ( $timings [$i] );
			}
		}

		return array_values ( $timings );
	}

	/**
	 * 返回数据库查询的统计结果
	 *
	 * @return array the first element indicates the number of SQL statements executed,
	 *         and the second element the total time spent in SQL execution.
	 */
	public function getDbProfiling()
	{
		$timings = $this->getProfiling ( [ 'yii\db\Command::query','yii\db\Command::execute' ] );
		$count = count ( $timings );
		$time = 0;
		foreach ( $timings as $timing ) {
			$time += $timing ['duration'];
		}
		return [ $count,$time ];
	}

	/**
	 * 计算指定日志消息的运行时间
	 *
	 * @param array $messages the log messages obtained from profiling
	 * @return array timings. Each element is an array consisting of these elements:
	 *         `info`, `category`, `timestamp`, `trace`, `level`, `duration`.
	 */
	public function calculateTimings($messages)
	{
		$timings = [ ];
		$stack = [ ];
		foreach ( $messages as $i => $log ) {
			list ( $token, $level, $category, $timestamp, $traces ) = $log;
			$log [5] = $i;
			if ($level == Logger::LEVEL_PROFILE_BEGIN) {
				$stack [] = $log;
			} elseif ($level == Logger::LEVEL_PROFILE_END) {
				if (($last = array_pop ( $stack )) !== null && $last [0] === $token) {
					$timings [$last [5]] = [ 'info' => $last [0],'category' => $last [2],'timestamp' => $last [3],'trace' => $last [4],'level' => count ( $stack ),'duration' => $timestamp - $last [3] ];
				}
			}
		}
		ksort ( $timings );
		return array_values ( $timings );
	}

	/**
	 * 获取日志级别对应的可读字符串
	 *
	 * @param integer $level the message level, e.g. [[LEVEL_ERROR]], [[LEVEL_WARNING]].
	 * @return string the text display of the level
	 */
	public static function getLevelName($level)
	{
		static $levels = [ self::LEVEL_ERROR => 'error',self::LEVEL_WARNING => 'warning',self::LEVEL_INFO => 'info',self::LEVEL_TRACE => 'trace',self::LEVEL_PROFILE_BEGIN => 'profile begin',self::LEVEL_PROFILE_END => 'profile end' ];
		return isset ( $levels [$level] ) ? $levels [$level] : 'unknown';
	}
}
