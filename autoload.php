<?php
/**
 * Query
 *
 * Free Query Builder / Database Abstraction Layer
 *
 * @package		Query
 * @author		Timothy J. Warren
 * @copyright	Copyright (c) 2012 - 2014
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

// Require some common functions
require(QBASE_PATH.'common.php');

/**
 * Load query classes
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

// End of autoload.php