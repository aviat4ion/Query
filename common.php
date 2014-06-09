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
 * Create a snake_case string from camelCase
 *
 * @see http://stackoverflow.com/questions/1993721/how-to-convert-camelcase-to-camel-case
 *
 * @param string $input
 * @return string
 */
function from_camel_case($input) {
	preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
	$ret = $matches[0];
	foreach ($ret as &$match) {
		$match = strtolower($match);// == strtoupper($match) ? strtolower($match) : lcfirst($match);
	}
	return implode('_', $ret);
}

// --------------------------------------------------------------------------

/**
 * Zip a set of arrays together on common keys
 *
 * The $zipper_input array is an array of arrays indexed by their place in the output
 * array.
 *
 * @param array $zipper_input
 * @return array
 */
function array_zipper(Array $zipper_input)
{
	$output = array();

	foreach($zipper_input as $append_key => $values)
	{
		foreach($values as $index => $value)
		{
			if ( ! isset($output[$index]))
			{
				$output[$index] = array();
			}
			$output[$index][$append_key] = $value;
		}
	}

	return $output;
}

// --------------------------------------------------------------------------

/**
 * Connection function
 *
 * Send an array or object as connection parameters to create a connection. If
 * the array or object has an 'alias' parameter, passing that string to this
 * function will return that connection. Passing no parameters returns the last
 * connection created.
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
		$p = new stdClass();

		foreach($params as $k => $v)
		{
			$p->$k = $v;
		}

		//$params = new ArrayObject($params, ArrayObject::STD_PROP_LIST | ArrayObject::ARRAY_AS_PROPS);

		// Otherwise, return a new connection
		return $cmanager->connect($p);
	}
// @codeCoverageIgnoreStart
}
// @codeCoverageIgnoreEnd
// End of common.php