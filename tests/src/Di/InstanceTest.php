<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Leaps Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace leapsunit\src\di;

use Leaps\Base\Service;
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
        $className = Service::className();
        $instance = Instance::of($className);

        $this->assertTrue($instance instanceof Instance);
        $this->assertTrue($instance->get($container) instanceof Service);
        $this->assertTrue(Instance::ensure($instance, $className, $container) instanceof Service);
        $this->assertTrue($instance->get($container) !== Instance::ensure($instance, $className, $container));
    }

    public function testEnsure()
    {
        $container = new Container;
        $container->set('db', [
            'className' => 'Leaps\Db\Connection',
            'dsn' => 'test',
        ]);

        $this->assertTrue(Instance::ensure('db', 'Leaps\Db\Connection', $container) instanceof Connection);
        $this->assertTrue(Instance::ensure(new Connection, 'Leaps\Db\Connection', $container) instanceof Connection);
        $this->assertTrue(Instance::ensure([
            'className' => 'Leaps\Db\Connection',
            'dsn' => 'test',
        ], 'Leaps\Db\Connection', $container) instanceof Connection);
    }
}
