<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2015 TintSoft
 * @license http://www.tintsoft.com/license/
 */

namespace Leaps\Web;

/**
 * Interface for classes that parse the raw request body into a parameters array.
 *
 * @author Dan Schmidt <danschmidt5189@gmail.com>
 * @since 2.0
 */
interface RequestParserInterface
{
    /**
     * Parses a HTTP request body.
     * @param string $rawBody the raw HTTP request body.
     * @param string $contentType the content type specified for the request body.
     * @return array parameters parsed from the request body
     */
    public function parse($rawBody, $contentType);
}
