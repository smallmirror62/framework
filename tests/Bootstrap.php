<?php

// ensure we get report on all possible php errors
error_reporting ( - 1 );

define ( 'LEAPS_ENABLE_ERROR_HANDLER', false );
define ( 'LEAPS_DEBUG', true );
$_SERVER ['SCRIPT_NAME'] = '/' . __DIR__;
$_SERVER ['SCRIPT_FILENAME'] = __FILE__;

// require composer autoloader if available
$composerAutoload = __DIR__ . '/../vendor/autoload.php';
if (is_file ( $composerAutoload )) {
	require_once ($composerAutoload);
} else {
	require_once (__DIR__ . '/../../../autoload.php');
}
require_once (__DIR__ . '/../Leaps.php');

Leaps::setAlias ( '@leapsunit', __DIR__ );

require_once (__DIR__ . '/TestCase.php');
