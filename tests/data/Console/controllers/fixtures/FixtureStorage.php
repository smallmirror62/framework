<?php

namespace leapsunit\data\console\controllers\fixtures;

class FixtureStorage
{

    public static $globalFixturesData = [];

    public static $firstFixtureData = [];

    public static $secondFixtureData = [];

    public static function clear()
    {
        static::$globalFixturesData = [];
        static::$firstFixtureData = [];
        static::$secondFixtureData = [];
    }

}
