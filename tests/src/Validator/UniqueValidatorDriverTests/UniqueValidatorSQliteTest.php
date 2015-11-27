<?php

namespace leapsunit\src\validators\UniqueValidatorDriverTests;

use leapsunit\src\validators\UniqueValidatorTest;

/**
 * @group validators
 */
class UniqueValidatorSQliteTest extends UniqueValidatorTest
{
    protected $driverName = 'sqlite';
}
