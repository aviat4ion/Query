<?php
/**
 * Query
 *
 * Free Query Builder / Database Abstraction Layer
 *
 * @package		Query
 * @author		Timothy J. Warren
 * @copyright	Copyright (c) 2012
 * @link 		https://github.com/aviat4ion/Query
 * @license		http://philsturgeon.co.uk/code/dbad-license
 */

// --------------------------------------------------------------------------

/**
 * Autoloader for loading available database classes
 */

/**
 * Reference to root path
 */
define('QBASE_PATH', dirname(__FILE__).'/');

/**
 * Path to driver classes
 */
define('QDRIVER_PATH', QBASE_PATH.'drivers/');

if ( ! function_exists('do_include'))
{
	/**
	 * Bulk directory loading workaround for use
	 * with array_map and glob
	 *
	 * @param string $path
	 * @return void
	 */
	function do_include($path)
	{
		require_once($path);
	}
}

// Load base classes
array_map('do_include', glob(QBASE_PATH.'classes/*.php'));

// Load PDO Drivers
foreach(pdo_drivers() as $d)
{
	$dir = QDRIVER_PATH.$d;

	if(is_dir($dir))
	{
		array_map('do_include', glob($dir.'/*.php'));
	}
}

// Load Firebird driver, if applicable
if (function_exists('fbird_connect'))
{
	array_map('do_include', glob(QDRIVER_PATH.'/firebird/*.php'));
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

	foreach($array as &$a)
	{
		$new_array[] = $a[$index];
	}

	return $new_array;
}

// End of autoload.php