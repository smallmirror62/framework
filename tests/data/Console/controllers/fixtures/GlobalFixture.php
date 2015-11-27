<?php

namespace leapsunit\data\console\controllers\fixtures;

use Leaps\Test\Fixture;

class GlobalFixture extends Fixture
{

    public function load()
    {
        FixtureStorage::$globalFixturesData[] = 'some data set for global fixture';
    }

    public function unload()
    {
        FixtureStorage::$globalFixturesData = [];
    }

}