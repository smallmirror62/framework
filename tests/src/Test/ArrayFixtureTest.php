<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Leaps Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace leapsunit\src\Test;

use leapsunit\TestCase;
use Leaps\Test\ArrayFixture;

class ArrayFixtureTest extends TestCase
{

    /**
     * @var \Leaps\Test\ArrayFixture
     */
    private $_fixture;

    protected function setUp()
    {
        parent::setUp();
        $this->_fixture = new ArrayFixture();
    }

    public function testLoadUnloadParticularFile()
    {
        $this->_fixture->dataFile = '@leapsunit/src/Test/data/array_fixture.php';
        $this->assertEmpty($this->_fixture->data, 'fixture data should be empty');

        $this->_fixture->load();

        $this->assertCount(2, $this->_fixture->data, 'fixture data should match needed total count');
        $this->assertEquals('customer1', $this->_fixture['customer1']['name'], 'first fixture data should match');
        $this->assertEquals('customer2@example.com', $this->_fixture['customer2']['email'], 'second fixture data should match');
    }

    public function testNothingToLoad()
    {
        $this->_fixture->dataFile = false;
        $this->assertEmpty($this->_fixture->data, 'fixture data should be empty');

        $this->_fixture->load();
        $this->assertEmpty($this->_fixture->data, 'fixture data should not be loaded');
    }

    /**
     * @expectedException \Leaps\Base\InvalidConfigException
     */
    public function testWrongDataFileException()
    {
        $this->_fixture->dataFile = 'wrong/fixtures/data/path/alias';
        $this->_fixture->load();
    }

}
