<?php

namespace leapsunit\data\console\controllers\fixtures;

use Leaps\Test\Fixture;

class SecondFixture extends Fixture
{
    public function load()
    {
        FixtureStorage::$secondFixtureData[] = 'some data set for second fixture';
    }

    public function unload()
    {
        FixtureStorage::$secondFixtureData = [];
    }

}
