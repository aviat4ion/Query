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
 *
 */

define('BASE_PATH', dirname(__FILE__).'/');
define('DRIVER_PATH', BASE_PATH.'drivers/');

// Load base classes
require_once(BASE_PATH.'db_pdo.php');
require_once(BASE_PATH.'query_builder.php');

// Load PDO Drivers
foreach(pdo_drivers() as $d)
{
	$f = DRIVER_PATH.$d.'.php';
	$fsql = DRIVER_PATH.$d."_sql.php";

	if(is_file($f) && $f !== 'firebird')
	{
		require_once($f);
		require_once($fsql);
	}
}

// Load Firebird driver, if applicable
if (function_exists('fbird_connect'))
{
	require_once(DRIVER_PATH.'firebird.php');
	require_once(DRIVER_PATH.'firebird_sql.php');
}

// End of autoload.php