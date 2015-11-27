<?php
namespace leapsunit\src\Validator\ExistValidatorDriverTests;

use leapsunit\src\Validator\ExistValidatorTest;

/**
 * @group validators
 */
class ExistValidatorPostgresTest extends ExistValidatorTest
{
    protected $driverName = 'pgsql';
}
