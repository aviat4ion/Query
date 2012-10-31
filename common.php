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
 * Global classes/functions that don't really fit anywhere else
 */
 
/**
 * Generic exception for bad drivers
 *
 * @package Query
 * @subpackage Query
 */
class BadDBDriverException extends InvalidArgumentException {}

// --------------------------------------------------------------------------

/**
 * Generic exception for bad connection strings
 *
 * @package Query
 * @subpackage Query
 */
class BadConnectionException extends UnexpectedValueException {}

// --------------------------------------------------------------------------

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

// --------------------------------------------------------------------------

if ( ! function_exists('mb_trim'))
{
	/**
	 * Multibyte-safe trim function
	 *
	 * @param string
	 * @return string
	 */
	function mb_trim($string)
	{
		return preg_replace("/(^\s+)|(\s+$)/us", "", $string);
	}
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

// End of common.php