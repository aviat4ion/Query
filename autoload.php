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
if ( ! defined('QBASE_PATH')) define('QBASE_PATH', dirname(__FILE__).'/src/');

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
	// Load by namespace
	$path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
	$file = QBASE_PATH . "{$path}.php";

	if (file_exists($file)) require_once($file);
});

// End of autoload.php