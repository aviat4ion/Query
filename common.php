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
 * Global functions that don't really fit anywhere else
 *
 * @package Query
 */

// --------------------------------------------------------------------------

if ( ! function_exists('do_include'))
{
	/**
	 * Bulk directory loading workaround for use
	 * with array_map and glob
	 *
	 * @subpackage Core
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
	 * @subpackage Core
	 * @param string $string
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
 * @subpackage Core
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

// --------------------------------------------------------------------------

/**
 * Connection function
 *
 * @subpackage Core
 * @param mixed $params
 * @return Query_Builder
 */
function Query($params = '')
{
	$cmanager = Connection_Manager::get_instance();

	// If you are getting a previously created connection
	if (is_scalar($params))
	{
		return $cmanager->get_connection($params);
	}
	elseif ( ! is_scalar($params))
	{
		// Otherwise, return a new connection
		return $cmanager->connect($params);
	}

}

// End of common.php