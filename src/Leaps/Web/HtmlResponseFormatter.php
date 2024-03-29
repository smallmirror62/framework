<?php

/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2015 TintSoft
 * @license http://www.tintsoft.com/license/
 */
namespace Leaps\Web;

use Leaps\Base\Service;

/**
 * HtmlResponseFormatter formats the given data into an HTML response content.
 *
 * It is used by [[Response]] to format response data.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class HtmlResponseFormatter extends Service implements ResponseFormatterInterface
{
	/**
	 *
	 * @var string the Content-Type header for the response
	 */
	public $contentType = 'text/html';
	
	/**
	 * Formats the specified response.
	 *
	 * @param Response $response the response to be formatted.
	 */
	public function format($response)
	{
		if (stripos ( $this->contentType, 'charset' ) === false) {
			$this->contentType .= '; charset=' . $response->charset;
		}
		$response->getHeaders ()->set ( 'Content-Type', $this->contentType );
		if ($response->data !== null) {
			$response->content = $response->data;
		}
	}
}
