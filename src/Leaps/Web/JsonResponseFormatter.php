<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2015 TintSoft
 * @license http://www.tintsoft.com/license/
 */

namespace Leaps\Web;

use Leaps;
use Leaps\Base\Service;
use Leaps\Helper\Json;

/**
 * JsonResponseFormatter formats the given data into a JSON or JSONP response content.
 *
 * It is used by [[Response]] to format response data.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class JsonResponseFormatter extends Service implements ResponseFormatterInterface
{
    /**
     * @var boolean whether to use JSONP response format. When this is true, the [[Response::data|response data]]
     * must be an array consisting of `data` and `callback` members. The latter should be a JavaScript
     * function name while the former will be passed to this function as a parameter.
     */
    public $useJsonp = false;


    /**
     * Formats the specified response.
     * @param Response $response the response to be formatted.
     */
    public function format($response)
    {
        if ($this->useJsonp) {
            $this->formatJsonp($response);
        } else {
            $this->formatJson($response);
        }
    }

    /**
     * Formats response data in JSON format.
     * @param Response $response
     */
    protected function formatJson($response)
    {
        $response->getHeaders()->set('Content-Type', 'application/json; charset=UTF-8');
        if ($response->data !== null) {
            $response->content = Json::encode($response->data);
        }
    }

    /**
     * Formats response data in JSONP format.
     * @param Response $response
     */
    protected function formatJsonp($response)
    {
        $response->getHeaders()->set('Content-Type', 'application/javascript; charset=UTF-8');
        if (is_array($response->data) && isset($response->data['data'], $response->data['callback'])) {
            $response->content = sprintf('%s(%s);', $response->data['callback'], Json::htmlEncode($response->data['data']));
        } elseif ($response->data !== null) {
            $response->content = '';
            Leaps::warning("The 'jsonp' response requires that the data be an array consisting of both 'data' and 'callback' elements.", __METHOD__);
        }
    }
}
