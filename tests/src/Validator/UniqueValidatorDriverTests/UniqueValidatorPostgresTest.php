<?php

namespace leapsunit\src\validators\UniqueValidatorDriverTests;

use leapsunit\src\validators\UniqueValidatorTest;

/**
 * @group validators
 */
class UniqueValidatorPostgresTest extends UniqueValidatorTest
{
    protected $driverName = 'pgsql';
}
