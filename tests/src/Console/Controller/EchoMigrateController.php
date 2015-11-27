<?php
namespace leapsunit\src\Console\Controller;

use Leaps\Console\Controller\MigrateController;

/**
 * MigrateController that writes output via echo instead of using output stream. Allows us to buffer it.
 */
class EchoMigrateController extends MigrateController
{
    /**
     * @inheritdoc
     */
    public function stdout($string)
    {
        echo $string;
    }
}