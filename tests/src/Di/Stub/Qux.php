<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Leaps Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace leapsunit\src\di\stubs;

use Leaps\Base\Object;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Qux extends Object implements QuxInterface
{
    public $a;

    public function __construct($a = 1, $config = [])
    {
        $this->a = $a;
        parent::__construct($config);
    }

    public function quxMethod()
    {
    }
}
