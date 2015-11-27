<?php

namespace leapsunit\src\Console\Controller;

use Leaps;
use leapsunit\TestCase;
use leapsunit\data\Console\Controller\Fixture\FixtureStorage;
use Leaps\Console\Controller\FixtureController;

/**
 * Unit test for [[\Leaps\Console\Controller\FixtureController]].
 * @see FixtureController
 *
 * @group console
 */
class FixtureControllerTest extends TestCase
{

    /**
     * @var \leapsunit\src\Console\Controller\FixtureConsoledController
     */
    private $_fixtureController;

    protected function setUp()
    {
        parent::setUp();

        $this->_fixtureController = Leaps::createObject([
            'class' => 'leapsunit\src\Console\Controller\FixtureConsoledController',
            'interactive' => false,
            'globalFixtures' => [],
            'namespace' => 'leapsunit\data\Console\Controller\Fixture',
        ],[null, null]); //id and module are null
    }

    protected function tearDown()
    {
        $this->_fixtureController = null;
        FixtureStorage::clear();

        parent::tearDown();
    }

    public function testLoadGlobalFixture()
    {
        $this->_fixtureController->globalFixtures = [
            '\leapsunit\data\Console\Controller\Fixture\Global'
        ];

        $this->_fixtureController->actionLoad('First');

        $this->assertCount(1, FixtureStorage::$globalFixturesData, 'global fixture data should be loaded');
        $this->assertCount(1, FixtureStorage::$firstFixtureData, 'first fixture data should be loaded');
    }

    public function testUnloadGlobalFixture()
    {
        $this->_fixtureController->globalFixtures = [
            '\leapsunit\data\Console\Controller\Fixture\Global'
        ];

        FixtureStorage::$globalFixturesData[] = 'some seeded global fixture data';
        FixtureStorage::$firstFixtureData[] = 'some seeded first fixture data';

        $this->assertCount(1, FixtureStorage::$globalFixturesData, 'global fixture data should be loaded');
        $this->assertCount(1, FixtureStorage::$firstFixtureData, 'first fixture data should be loaded');

        $this->_fixtureController->actionUnload('First');

        $this->assertEmpty(FixtureStorage::$globalFixturesData, 'global fixture data should be unloaded');
        $this->assertEmpty(FixtureStorage::$firstFixtureData, 'first fixture data should be unloaded');
    }

    public function testLoadAll()
    {
        $this->assertEmpty(FixtureStorage::$globalFixturesData, 'global fixture data should be empty');
        $this->assertEmpty(FixtureStorage::$firstFixtureData, 'first fixture data should be empty');
        $this->assertEmpty(FixtureStorage::$secondFixtureData, 'second fixture data should be empty');

        $this->_fixtureController->actionLoad('*');

        $this->assertCount(1, FixtureStorage::$globalFixturesData, 'global fixture data should be loaded');
        $this->assertCount(1, FixtureStorage::$firstFixtureData, 'first fixture data should be loaded');
        $this->assertCount(1, FixtureStorage::$secondFixtureData, 'second fixture data should be loaded');
    }

    public function testUnloadAll()
    {
        FixtureStorage::$globalFixturesData[] = 'some seeded global fixture data';
        FixtureStorage::$firstFixtureData[] = 'some seeded first fixture data';
        FixtureStorage::$secondFixtureData[] = 'some seeded second fixture data';

        $this->assertCount(1, FixtureStorage::$globalFixturesData, 'global fixture data should be loaded');
        $this->assertCount(1, FixtureStorage::$firstFixtureData, 'first fixture data should be loaded');
        $this->assertCount(1, FixtureStorage::$secondFixtureData, 'second fixture data should be loaded');

        $this->_fixtureController->actionUnload('*');

        $this->assertEmpty(FixtureStorage::$globalFixturesData, 'global fixture data should be unloaded');
        $this->assertEmpty(FixtureStorage::$firstFixtureData, 'first fixture data should be unloaded');
        $this->assertEmpty(FixtureStorage::$secondFixtureData, 'second fixture data should be unloaded');
    }

    public function testLoadParticularExceptOnes()
    {
        $this->_fixtureController->actionLoad('First', '-Second', '-Global');

        $this->assertCount(1, FixtureStorage::$firstFixtureData, 'first fixture data should be loaded');
        $this->assertEmpty(FixtureStorage::$globalFixturesData, 'global fixture data should not be loaded');
        $this->assertEmpty(FixtureStorage::$secondFixtureData, 'second fixture data should not be loaded');
    }

    public function testUnloadParticularExceptOnes()
    {
        FixtureStorage::$globalFixturesData[] = 'some seeded global fixture data';
        FixtureStorage::$firstFixtureData[] = 'some seeded first fixture data';
        FixtureStorage::$secondFixtureData[] = 'some seeded second fixture data';

        $this->_fixtureController->actionUnload('First', '-Second', '-Global');

        $this->assertEmpty(FixtureStorage::$firstFixtureData, 'first fixture data should be unloaded');
        $this->assertNotEmpty(FixtureStorage::$globalFixturesData, 'global fixture data should not be unloaded');
        $this->assertNotEmpty(FixtureStorage::$secondFixtureData, 'second fixture data should not be unloaded');
    }

    public function testLoadAllExceptOnes()
    {
        $this->_fixtureController->actionLoad('*', '-Second', '-Global');

        $this->assertCount(1, FixtureStorage::$firstFixtureData, 'first fixture data should be loaded');
        $this->assertEmpty(FixtureStorage::$globalFixturesData, 'global fixture data should not be loaded');
        $this->assertEmpty(FixtureStorage::$secondFixtureData, 'second fixture data should not be loaded');
    }

    public function testUnloadAllExceptOnes()
    {
        FixtureStorage::$globalFixturesData[] = 'some seeded global fixture data';
        FixtureStorage::$firstFixtureData[] = 'some seeded first fixture data';
        FixtureStorage::$secondFixtureData[] = 'some seeded second fixture data';

        $this->_fixtureController->actionUnload('*', '-Second', '-Global');

        $this->assertEmpty(FixtureStorage::$firstFixtureData, 'first fixture data should be unloaded');
        $this->assertNotEmpty(FixtureStorage::$globalFixturesData, 'global fixture data should not be unloaded');
        $this->assertNotEmpty(FixtureStorage::$secondFixtureData, 'second fixture data should not be unloaded');
    }

    public function testNothingToLoadParticularExceptOnes()
    {
        $this->_fixtureController->actionLoad('First', '-First');

        $this->assertEmpty(FixtureStorage::$firstFixtureData, 'first fixture data should not be loaded');
    }

    public function testNothingToUnloadParticularExceptOnes()
    {
        $this->_fixtureController->actionUnload('First', '-First');

        $this->assertEmpty(FixtureStorage::$firstFixtureData, 'first fixture data should not be loaded');
    }

    /**
     * @expectedException \Leaps\Console\Exception
     */
    public function testNoFixturesWereFoundInLoad()
    {
        $this->_fixtureController->actionLoad('NotExistingFixture');
    }

    /**
     * @expectedException \Leaps\Console\Exception
     */
    public function testNoFixturesWereFoundInUnload()
    {
        $this->_fixtureController->actionUnload('NotExistingFixture');
    }

}

class FixtureConsoledController extends FixtureController
{

    public function stdout($string)
    {
    }

}
