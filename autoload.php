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
 * Autoloader for loading available database classes
 */

define('BASE_PATH', dirname(__FILE__).'/');
define('DRIVER_PATH', BASE_PATH.'drivers/');

// Bulk loading wrapper workaround for PHP < 5.4
if ( ! function_exists('do_include'))
{
	function do_include($path)
	{
		require_once($path);
	}
}

// Load base classes
array_map('do_include', glob(BASE_PATH.'classes/*.php'));

// Load PDO Drivers
foreach(pdo_drivers() as $d)
{
	$dir = DRIVER_PATH.$d;

	if(is_dir($dir))
	{
		array_map('do_include', glob($dir.'/*.php'));
	}
}

// Load Firebird driver, if applicable
if (function_exists('fbird_connect'))
{
	array_map('do_include', glob(DRIVER_PATH.'/firebird/*.php'));
}

// --------------------------------------------------------------------------

/**
 * Filter out db rows into one array
 *
 * @param array $array
 * @param mixed $index
 * @return array
 */
function db_filter($array, $index)
{
	$new_array = array();

	foreach($array as $a)
	{
		$new_array[] = $a[$index];
	}

	return $new_array;
}

// End of autoload.php