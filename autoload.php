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

/**
 * Load a Query class
 *
 * @param string $class
 */
function query_autoload($class)
{
	$class = strtolower($class);

	// Load Firebird separately
	if (function_exists('fbird_connect') && $class === 'firebird')
	{
		array_map('do_include', glob(QDRIVER_PATH.'/firebird/*.php'));
		return;
	}

	$class_path = QBASE_PATH . "classes/{$class}.php";

	$driver_path = QDRIVER_PATH . "{$class}";

	if (is_file($class_path))
	{
		require_once($class_path);
	}
	elseif (is_dir($driver_path))
	{
		if (in_array($class, PDO::getAvailableDrivers()))
		{
			array_map('do_include', glob("{$driver_path}/*.php"));
		}
	}
}

// Set up autoloader
spl_autoload_register('query_autoload');

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