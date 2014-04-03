<?php
/**
 * Query
 *
 * Free Query Builder / Database Abstraction Layer
 *
 * @package		Query
 * @subpackage	Core
 * @author		Timothy J. Warren
 * @copyright	Copyright (c) 2012 - 2014
 * @link 		https://github.com/aviat4ion/Query
 * @license		http://philsturgeon.co.uk/code/dbad-license
 */

// --------------------------------------------------------------------------

/**
 * Global functions that don't really fit anywhere else
 */

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
 * @param string|object|array $params
 * @return Query\Query_Builder|null
 */
function Query($params = '')
{
	$cmanager = \Query\Connection_Manager::get_instance();

	// If you are getting a previously created connection
	if (is_scalar($params))
	{
		return $cmanager->get_connection($params);
	}
	elseif ( ! is_scalar($params) && ! is_null($params))
	{
		$params = new ArrayObject($params, ArrayObject::STD_PROP_LIST | ArrayObject::ARRAY_AS_PROPS);

		// Otherwise, return a new connection
		return $cmanager->connect($params);
	}
// @codeCoverageIgnoreStart
}
// @codeCoverageIgnoreEnd
// End of common.php