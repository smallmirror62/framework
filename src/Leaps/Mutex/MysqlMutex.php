<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2015 TintSoft
 * @license http://www.tintsoft.com/license/
 */

namespace Leaps\Mutex;

use Leaps;
use Leaps\Base\InvalidConfigException;

/**
 * MysqlMutex implements mutex "lock" mechanism via MySQL locks.
 *
 * Application configuration example:
 *
 * ```
 * [
 *     'components' => [
 *         'db' => [
 *             'className' => 'Leaps\Db\Connection',
 *             'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
 *         ]
 *         'mutex' => [
 *             'className' => 'Leaps\Mutex\MysqlMutex',
 *         ],
 *     ],
 * ]
 * ```
 *
 * @see Mutex
 *
 * @author resurtm <resurtm@gmail.com>
 * @since 2.0
 */
class MysqlMutex extends DbMutex
{
    /**
     * Initializes MySQL specific mutex component implementation.
     * @throws InvalidConfigException if [[db]] is not MySQL connection.
     */
    public function init()
    {
        parent::init();
        if ($this->db->driverName !== 'mysql') {
            throw new InvalidConfigException('In order to use MysqlMutex connection must be configured to use MySQL database.');
        }
    }

    /**
     * Acquires lock by given name.
     * @param string $name of the lock to be acquired.
     * @param integer $timeout to wait for lock to become released.
     * @return boolean acquiring result.
     * @see http://dev.mysql.com/doc/refman/5.0/en/miscellaneous-functions.html#function_get-lock
     */
    protected function acquireLock($name, $timeout = 0)
    {
        return (bool) $this->db
            ->createCommand('SELECT GET_LOCK(:name, :timeout)', [':name' => $name, ':timeout' => $timeout])
            ->queryScalar();
    }

    /**
     * Releases lock by given name.
     * @param string $name of the lock to be released.
     * @return boolean release result.
     * @see http://dev.mysql.com/doc/refman/5.0/en/miscellaneous-functions.html#function_release-lock
     */
    protected function releaseLock($name)
    {
        return (bool) $this->db
            ->createCommand('SELECT RELEASE_LOCK(:name)', [':name' => $name])
            ->queryScalar();
    }
}
