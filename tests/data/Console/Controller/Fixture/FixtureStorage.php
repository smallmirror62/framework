<?php

namespace leapsunit\data\Console\Controller\Fixture;

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
