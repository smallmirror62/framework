<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2015 TintSoft
 * @license http://www.tintsoft.com/license/
 */

namespace Leaps\Web;

/**
 * UnauthorizedHttpException represents an "Unauthorized" HTTP exception with status code 401
 *
 * Use this exception to indicate that a client needs to authenticate or login
 * to perform the requested action. If the client is already authenticated and
 * is simply not allowed to perform the action, consider using a 403
 * [[ForbiddenHttpException]] or 404 [[NotFoundHttpException]] instead.
 *
 * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html#sec10.4.2
 * @author Dan Schmidt <danschmidt5189@gmail.com>
 * @since 2.0
 */
class UnauthorizedHttpException extends HttpException
{
    /**
     * Constructor.
     * @param string $message error message
     * @param integer $code error code
     * @param \Exception $previous The previous exception used for the exception chaining.
     */
    public function __construct($message = null, $code = 0, \Exception $previous = null)
    {
        parent::__construct(401, $message, $code, $previous);
    }
}
