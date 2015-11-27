<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Leaps Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace leapsunit\src\web\stubs;

use Leaps\Base\Object;
use leapsunit\src\di\stubs\QuxInterface;

/**
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 2.0
 */
class OtherQux extends Object implements QuxInterface
{
    public $b;
    public function quxMethod()
    {
        return 'other_qux';
    }
}
