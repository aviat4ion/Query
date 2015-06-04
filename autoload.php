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
 *
 * @package Query
 */

namespace Query;

/**
 * Reference to root path
 * @subpackage Core
 */
if ( ! defined('QBASE_PATH')) define('QBASE_PATH', dirname(__FILE__).'/');

/**
 * Path to driver classes
 * @subpackage Core
 */
if ( ! defined('QDRIVER_PATH')) define('QDRIVER_PATH', QBASE_PATH.'drivers/');

// Require some common functions
require(QBASE_PATH.'common.php');

// Load Query Classes
spl_autoload_register(function ($class)
{
	$class_segments = explode('\\', $class);
	$driver_class = strtolower(array_pop($class_segments));

	// Load DB Driver classes
	$driver_path = QDRIVER_PATH . "{$driver_class}";
	if ($class_segments == array('Query', 'Driver') && is_dir($driver_path))
	{

		// Firebird is a special case, since it's not a PDO driver
		// @codeCoverageIgnoreStart
		if (
			in_array($driver_class, \PDO::getAvailableDrivers())
			||  function_exists('\\fbird_connect') && $driver_class === 'firebird'
		)
		{
			array_map('\\do_include', glob("{$driver_path}/*.php"));
		}
		// @codeCoverageIgnoreEnd
	}

	// Load other classes
	$path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
	$file = QBASE_PATH . "{$path}.php";

	// @codeCoverageIgnoreStart
	if (file_exists($file))
	{
		require_once($file);
	}
	// @codeCoverageIgnoreEnd
});

// End of autoload.php