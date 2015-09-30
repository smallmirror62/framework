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
namespace Leaps\Http\Response;

use Leaps\Di\Injectable;

class HtmlFormatter extends Injectable implements ResponseFormatterInterface
{
	/**
	 * 响应的内容类型
	 *
	 * @var string
	 */
	public $contentType = 'text/html';

	/**
	 * 格式化指定的响应
	 *
	 * @param Response $response
	 */
	public function format($response)
	{
		if (stripos ( $this->contentType, 'charset' ) === false) {
			$this->contentType .= '; charset=' . $response->charset;
		}
		$response->getHeaders ()->set ( 'Content-Type', $this->contentType );
		$response->content = $response->data;
	}
}