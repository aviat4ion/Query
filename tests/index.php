<?php
/**
 * Query
 *
 * Free Query Builder / Database Abstraction Layer
 *
 * @author 		Timothy J. Warren
 * @copyright	Copyright (c) 2012
 * @link 		https://github.com/aviat4ion/Query
 * @license 	http://philsturgeon.co.uk/code/dbad-license 
 */

// --------------------------------------------------------------------------

/**
 * Unit test bootstrap - Using php simpletest
 */
define('BASE_DIR', '../');
define('TEST_DIR', dirname(__FILE__));
define('DS', DIRECTORY_SEPARATOR);

// Include simpletest
// it has to be set in your php path, or put in the tests folder
require_once('simpletest/autorun.php');


// Bulk loading wrapper workaround for PHP < 5.4
function do_include($path)
{
	require_once($path);
}

// Include core tests;
require_once(BASE_DIR.'db_pdo.php');
require_once(BASE_DIR.'query_builder.php');


// Include db tests
// Load db classes based on capability
$src_path = BASE_DIR.'drivers/';
$test_path = './databases/';

foreach(pdo_drivers() as $d)
{
	// PDO firebird isn't stable enough to 
	// bother, so skip it.
	if ($d === 'firebird')
	{
		continue;
	}

	$src_file = "{$src_path}{$d}.php";
	
	if(is_file($src_file))
	{
		require_once("{$src_path}{$d}.php");
		require_once("{$src_path}{$d}_sql.php");
		require_once("{$test_path}{$d}.php");
		require_once("{$test_path}{$d}-qb.php");
	}
}

// Load Firebird if there is support
if(function_exists('ibase_connect'))
{
	require_once("{$src_path}firebird-ibase.php");
	require_once("{$src_path}firebird_sql.php");
	require_once("{$test_path}firebird.php");
	require_once("{$test_path}firebird-qb.php");
}