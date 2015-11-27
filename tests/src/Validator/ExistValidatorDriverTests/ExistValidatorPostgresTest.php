<?php
namespace leapsunit\src\validators\ExistValidatorDriverTests;

use leapsunit\src\validators\ExistValidatorTest;

/**
 * @group validators
 */
class ExistValidatorPostgresTest extends ExistValidatorTest
{
    protected $driverName = 'pgsql';
}
