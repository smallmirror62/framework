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

class Response
{
	/**
	 * 退出代码
	 * @var integer the exit status. Exit statuses should be in the range 0 to 254.
	 *      The status 0 means the program terminates successfully.
	 */
	public $exitStatus = 0;

	/**
	 * 发送响应到客户端
	 */
	public function send()
	{
	}

	/**
	 * 删除所有输出缓存
	 */
	public function clearOutputBuffers()
	{
		// the following manual level counting is to deal with zlib.output_compression set to On
		for($level = ob_get_level (); $level > 0; -- $level) {
			if (! @ob_end_clean ()) {
				ob_clean ();
			}
		}
	}
}