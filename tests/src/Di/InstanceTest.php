<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Leaps Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace leapsunit\src\di;

use Leaps\Base\Component;
use Leaps\Db\Connection;
use Leaps\Di\Container;
use Leaps\Di\Instance;
use leapsunit\TestCase;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class InstanceTest extends TestCase
{
    public function testOf()
    {
        $container = new Container;
        $className = Component::className();
        $instance = Instance::of($className);

        $this->assertTrue($instance instanceof Instance);
        $this->assertTrue($instance->get($container) instanceof Component);
        $this->assertTrue(Instance::ensure($instance, $className, $container) instanceof Component);
        $this->assertTrue($instance->get($container) !== Instance::ensure($instance, $className, $container));
    }

    public function testEnsure()
    {
        $container = new Container;
        $container->set('db', [
            'class' => 'Leaps\Db\Connection',
            'dsn' => 'test',
        ]);

        $this->assertTrue(Instance::ensure('db', 'Leaps\Db\Connection', $container) instanceof Connection);
        $this->assertTrue(Instance::ensure(new Connection, 'Leaps\Db\Connection', $container) instanceof Connection);
        $this->assertTrue(Instance::ensure([
            'class' => 'Leaps\Db\Connection',
            'dsn' => 'test',
        ], 'Leaps\Db\Connection', $container) instanceof Connection);
    }
}
