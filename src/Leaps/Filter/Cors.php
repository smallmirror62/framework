<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2015 TintSoft
 * @license http://www.tintsoft.com/license/
 */

namespace Leaps\Filter;

use Leaps;
use Leaps\Base\ActionFilter;
use Leaps\Web\Request;
use Leaps\Web\Response;

/**
 * Cors filter implements [Cross Origin Resource Sharing](http://en.wikipedia.org/wiki/Cross-origin_resource_sharing).
 * Make sure to read carefully what CORS does and does not. CORS do not secure your API,
 * but allow the developer to grant access to third party code (ajax calls from external domain)
 *
 * You may use CORS filter by attaching it as a behavior to a controller or module, like the following,
 *
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         'corsFilter' => [
 *             'className' => \Leaps\Filter\Cors::className(),
 *         ],
 *     ];
 * }
 * ```
 *
 * The CORS filter can be specialized to restrict parameters, like this,
 * [MDN CORS Information](https://developer.mozilla.org/en-US/docs/Web/HTTP/Access_control_CORS)
 *
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         'corsFilter' => [
 *             'className' => \Leaps\Filter\Cors::className(),
 *             'cors' => [
 *                 // restrict access to
 *                 'Origin' => ['http://www.myserver.com', 'https://www.myserver.com'],
 *                 'Access-Control-Request-Method' => ['POST', 'PUT'],
 *                 // Allow only POST and PUT methods
 *                 'Access-Control-Request-Headers' => ['X-Wsse'],
 *                 // Allow only headers 'X-Wsse'
 *                 'Access-Control-Allow-Credentials' => true,
 *                 // Allow OPTIONS caching
 *                 'Access-Control-Max-Age' => 3600,
 *                 // Allow the X-Pagination-Current-Page header to be exposed to the browser.
 *                 'Access-Control-Expose-Headers' => ['X-Pagination-Current-Page'],
 *             ],
 *
 *         ],
 *     ];
 * }
 * ```
 *
 * @author Philippe Gaultier <pgaultier@gmail.com>
 * @since 2.0
 */
class Cors extends ActionFilter
{
    /**
     * @var Request the current request. If not set, the `request` application component will be used.
     */
    public $request;
    /**
     * @var Response the response to be sent. If not set, the `response` application component will be used.
     */
    public $response;
    /**
     * @var array define specific CORS rules for specific actions
     */
    public $actions = [];
    /**
     * @var array Basic headers handled for the CORS requests.
     */
    public $cors = [
        'Origin' => ['*'],
        'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
        'Access-Control-Request-Headers' => ['*'],
        'Access-Control-Allow-Credentials' => null,
        'Access-Control-Max-Age' => 86400,
        'Access-Control-Expose-Headers' => [],
    ];


    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $this->request = $this->request ?: Leaps::$app->getRequest();
        $this->response = $this->response ?: Leaps::$app->getResponse();

        $this->overrideDefaultSettings($action);

        $requestCorsHeaders = $this->extractHeaders();
        $responseCorsHeaders = $this->prepareHeaders($requestCorsHeaders);
        $this->addCorsHeaders($this->response, $responseCorsHeaders);

        return true;
    }

    /**
     * Override settings for specific action
     * @param \Leaps\Base\Action $action the action settings to override
     */
    public function overrideDefaultSettings($action)
    {
        if (isset($this->actions[$action->id])) {
            $actionParams = $this->actions[$action->id];
            $actionParamsKeys = array_keys($actionParams);
            foreach ($this->cors as $headerField => $headerValue) {
                if (in_array($headerField, $actionParamsKeys)) {
                    $this->cors[$headerField] = $actionParams[$headerField];
                }
            }
        }
    }

    /**
     * Extract CORS headers from the request
     * @return array CORS headers to handle
     */
    public function extractHeaders()
    {
        $headers = [];
        $requestHeaders = array_keys($this->cors);
        foreach ($requestHeaders as $headerField) {
            $serverField = $this->headerizeToPhp($headerField);
            $headerData = isset($_SERVER[$serverField]) ? $_SERVER[$serverField] : null;
            if ($headerData !== null) {
                $headers[$headerField] = $headerData;
            }
        }
        return $headers;
    }

    /**
     * For each CORS headers create the specific response
     * @param array $requestHeaders CORS headers we have detected
     * @return array CORS headers ready to be sent
     */
    public function prepareHeaders($requestHeaders)
    {
        $responseHeaders = [];
        // handle Origin
        if (isset($requestHeaders['Origin'], $this->cors['Origin'])) {
            if (in_array('*', $this->cors['Origin']) || in_array($requestHeaders['Origin'], $this->cors['Origin'])) {
                $responseHeaders['Access-Control-Allow-Origin'] = $requestHeaders['Origin'];
            }
        }

        $this->prepareAllowHeaders('Headers', $requestHeaders, $responseHeaders);

        if (isset($requestHeaders['Access-Control-Request-Method'])) {
            $responseHeaders['Access-Control-Allow-Methods'] = implode(', ', $this->cors['Access-Control-Request-Method']);
        }

        if (isset($this->cors['Access-Control-Allow-Credentials'])) {
            $responseHeaders['Access-Control-Allow-Credentials'] = $this->cors['Access-Control-Allow-Credentials'] ? 'true' : 'false';
        }

        if (isset($this->cors['Access-Control-Max-Age']) && Leaps::$app->getRequest()->getIsOptions()) {
            $responseHeaders['Access-Control-Max-Age'] = $this->cors['Access-Control-Max-Age'];
        }

        if (isset($this->cors['Access-Control-Expose-Headers'])) {
            $responseHeaders['Access-Control-Expose-Headers'] = implode(', ', $this->cors['Access-Control-Expose-Headers']);
        }

        return $responseHeaders;
    }

    /**
     * Handle classic CORS request to avoid duplicate code
     * @param string $type the kind of headers we would handle
     * @param array $requestHeaders CORS headers request by client
     * @param array $responseHeaders CORS response headers sent to the client
     */
    protected function prepareAllowHeaders($type, $requestHeaders, &$responseHeaders)
    {
        $requestHeaderField = 'Access-Control-Request-' . $type;
        $responseHeaderField = 'Access-Control-Allow-' . $type;
        if (!isset($requestHeaders[$requestHeaderField], $this->cors[$requestHeaderField])) {
            return;
        }
        if (in_array('*', $this->cors[$requestHeaderField])) {
            $responseHeaders[$responseHeaderField] = $this->headerize($requestHeaders[$requestHeaderField]);
        } else {
            $requestedData = preg_split("/[\\s,]+/", $requestHeaders[$requestHeaderField], -1, PREG_SPLIT_NO_EMPTY);
            $acceptedData = array_uintersect($requestedData, $this->cors[$requestHeaderField], 'strcasecmp');
            if (!empty($acceptedData)) {
                $responseHeaders[$responseHeaderField] = implode(', ', $acceptedData);
            }
        }
    }

    /**
     * Adds the CORS headers to the response
     * @param Response $response
     * @param array CORS headers which have been computed
     */
    public function addCorsHeaders($response, $headers)
    {
        if (empty($headers) === false) {
            $responseHeaders = $response->getHeaders();
            foreach ($headers as $field => $value) {
                $responseHeaders->set($field, $value);
            }
        }
    }

    /**
     * Convert any string (including php headers with HTTP prefix) to header format like :
     *  * X-PINGOTHER -> X-Pingother
     *  * X_PINGOTHER -> X-Pingother
     * @param string $string string to convert
     * @return string the result in "header" format
     */
    protected function headerize($string)
    {
        $headers = preg_split("/[\\s,]+/", $string, -1, PREG_SPLIT_NO_EMPTY);
        $headers = array_map(function ($element) {
            return str_replace(' ', '-', ucwords(strtolower(str_replace(['_', '-'], [' ', ' '], $element))));
        }, $headers);
        return implode(', ', $headers);
    }

    /**
     * Convert any string (including php headers with HTTP prefix) to header format like :
     *  * X-Pingother -> HTTP_X_PINGOTHER
     *  * X PINGOTHER -> HTTP_X_PINGOTHER
     * @param string $string string to convert
     * @return string the result in "php $_SERVER header" format
     */
    protected function headerizeToPhp($string)
    {
        return 'HTTP_' . strtoupper(str_replace([' ', '-'], ['_', '_'], $string));
    }
}
