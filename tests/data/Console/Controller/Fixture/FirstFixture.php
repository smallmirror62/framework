<?php

namespace leapsunit\data\Console\Controller\Fixture;

use Leaps\Test\Fixture;

class FirstFixture extends Fixture
{
    public function load()
    {
        FixtureStorage::$firstFixtureData[] = 'some data set for first fixture';
    }

    public function unload()
    {
        FixtureStorage::$firstFixtureData = [];
    }

}
