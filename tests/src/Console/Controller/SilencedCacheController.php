<?php
namespace leapsunit\src\Console\Controller;


use Leaps\Console\Controller\CacheController;


/**
 * CacheController that discards output.
 */
class SilencedCacheController extends CacheController
{
    /**
     * @inheritdoc
     */
    public function stdout($string)
    {
        // do nothing
    }
}