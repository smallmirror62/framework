<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2015 TintSoft
 * @license http://www.tintsoft.com/license/
 */

namespace Leaps\Web;

/**
 * UnsupportedMediaTypeHttpException represents an "Unsupported Media Type" HTTP exception with status code 415
 *
 * Use this exception when the client sends data in a format that your
 * application does not understand. For example, you would throw this exception
 * if the client POSTs XML data to an action or controller that only accepts
 * JSON.
 *
 * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html#sec10.4.16
 * @author Dan Schmidt <danschmidt5189@gmail.com>
 * @since 2.0
 */
class UnsupportedMediaTypeHttpException extends HttpException
{
    /**
     * Constructor.
     * @param string $message error message
     * @param integer $code error code
     * @param \Exception $previous The previous exception used for the exception chaining.
     */
    public function __construct($message = null, $code = 0, \Exception $previous = null)
    {
        parent::__construct(415, $message, $code, $previous);
    }
}
