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
class Foo extends Object
{
    public $bar;

    public function __construct(Bar $bar, $config = [])
    {
        $this->bar = $bar;
        parent::__construct($config);
    }
}
