<?php

namespace leapsunit\src\Validator\UniqueValidatorDriverTests;

use leapsunit\src\Validator\UniqueValidatorTest;

/**
 * @group validators
 */
class UniqueValidatorPostgresTest extends UniqueValidatorTest
{
    protected $driverName = 'pgsql';
}
