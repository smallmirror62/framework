<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2015 TintSoft
 * @license http://www.tintsoft.com/license/
 */

namespace Leaps\Cache;

use Leaps;
use Leaps\Base\InvalidConfigException;

/**
 * FileDependency represents a dependency based on a file's last modification time.
 *
 * If the last modification time of the file specified via [[fileName]] is changed,
 * the dependency is considered as changed.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class FileDependency extends Dependency
{
    /**
     * @var string the file path or path alias whose last modification time is used to
     * check if the dependency has been changed.
     */
    public $fileName;


    /**
     * Generates the data needed to determine if dependency has been changed.
     * This method returns the file's last modification time.
     * @param Cache $cache the cache component that is currently evaluating this dependency
     * @return mixed the data needed to determine if dependency has been changed.
     * @throws InvalidConfigException if [[fileName]] is not set
     */
    protected function generateDependencyData($cache)
    {
        if ($this->fileName === null) {
            throw new InvalidConfigException('FileDependency::fileName must be set');
        }

        return @filemtime(Leaps::getAlias($this->fileName));
    }
}
