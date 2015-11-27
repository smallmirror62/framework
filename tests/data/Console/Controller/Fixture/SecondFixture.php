<?php

namespace leapsunit\data\Console\Controller\Fixture;

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
