<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2015 TintSoft
 * @license http://www.tintsoft.com/license/
 */

namespace Leaps\Web;

use Leaps\Base\Object;

/**
 * JsExpression marks a string as a JavaScript expression.
 *
 * When using [[\Leaps\Helper\Json::encode()]] or [[\Leaps\Helper\Json::htmlEncode()]] to encode a value, JsonExpression objects
 * will be specially handled and encoded as a JavaScript expression instead of a string.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class JsExpression extends Object
{
    /**
     * @var string the JavaScript expression represented by this object
     */
    public $expression;


    /**
     * Constructor.
     * @param string $expression the JavaScript expression represented by this object
     * @param array $config additional configurations for this object
     */
    public function __construct($expression, $config = [])
    {
        $this->expression = $expression;
        parent::__construct($config);
    }

    /**
     * The PHP magic function converting an object into a string.
     * @return string the JavaScript expression.
     */
    public function __toString()
    {
        return $this->expression;
    }
}
