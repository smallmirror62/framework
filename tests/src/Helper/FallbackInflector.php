<?php


namespace leapsunit\src\helpers;


use Leaps\Helper\BaseInflector;

/**
 * Forces Inflector::slug to use PHP even if intl is available
 */
class FallbackInflector extends BaseInflector
{
    /**
     * @inheritdoc
     */
    protected static function hasIntl()
    {
        return false;
    }
}