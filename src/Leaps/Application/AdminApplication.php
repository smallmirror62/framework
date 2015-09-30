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
namespace Leaps\Application;

class AdminApplication extends \Leaps\Core\Application
{

	/**
	 * (non-PHPdoc)
	 *
	 * @param resource Leaps\Http\Request
	 * @see \Leaps\Core\Application::handleRequest()
	 */
	public function handleRequest($request)
	{
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \Leaps\Core\Application::coreServices()
	 */
	public function coreServices()
	{
		return [
				"cookie" => [
						"className" => "\\Leaps\\Http\\Cookies"
				],
				"request" => [
						"className" => "\\Leaps\\Http\\Request"
				],
				"response" => [
						"className" => "\\Leaps\\Http\\Response"
				]
		];
	}
}