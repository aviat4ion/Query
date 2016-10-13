<?php declare(strict_types=1);
/**
 * Query
 *
 * SQL Query Builder / Database Abstraction Layer
 *
 * PHP version 7
 *
 * @package     Query
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2012 - 2016 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link        https://git.timshomepage.net/aviat4ion/Query
 */


use Query\ConnectionManager;

require __DIR__ . '/../vendor/autoload.php';

// --------------------------------------------------------------------------

/**
 * Global functions that don't really fit anywhere else
 */

if ( ! function_exists('mb_trim'))
{
	/**
	 * Multibyte-safe trim function
	 *
	 * @param string $string
	 * @return string
	 */
	function mb_trim(string $string): string
	{
		return preg_replace("/(^\s+)|(\s+$)/us", "", $string);
	}
}

// --------------------------------------------------------------------------

if ( ! function_exists('db_filter'))
{
	/**
	 * Filter out db rows into one array
	 *
	 * @param array $array
	 * @param mixed $index
	 * @return array
	 */
	function db_filter(array $array, $index): array
	{
		$new_array = [];

		foreach($array as $a)
		{
			$new_array[] = $a[$index];
		}

		return $new_array;
	}
}

// --------------------------------------------------------------------------

if ( ! function_exists('from_camel_case'))
{
	/**
	 * Create a snake_case string from camelCase
	 *
	 * @see http://stackoverflow.com/questions/1993721/how-to-convert-camelcase-to-camel-case
	 *
	 * @param string $input
	 * @return string
	 */
	function from_camel_case(string $input): string
	{
		preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
		$ret = $matches[0];
		foreach ($ret as &$match) {
			$match = strtolower($match);
		}
		return implode('_', $ret);
	}
}

if ( ! function_exists('to_camel_case'))
{
	/**
	 * Create a camelCase string from snake_case
	 *
	 * @param string $snake_case
	 * @return string
	 */
	function to_camel_case(string $snake_case): string
	{
		$pieces = explode('_', $snake_case);

		$pieces[0] = mb_strtolower($pieces[0]);
		for($i = 1; $i < count($pieces); $i++)
		{
			$pieces[$i] = ucfirst(mb_strtolower($pieces[$i]));
		}

		return implode('', $pieces);
	}
}

// --------------------------------------------------------------------------

if ( ! function_exists('array_zipper'))
{
	/**
	 * Zip a set of arrays together on common keys
	 *
	 * The $zipper_input array is an array of arrays indexed by their place in the output
	 * array.
	 *
	 * @param array $zipper_input
	 * @return array
	 */
	function array_zipper(array $zipper_input): array
	{
		$output = [];

		foreach($zipper_input as $append_key => $values)
		{
			foreach($values as $index => $value)
			{
				if ( ! isset($output[$index]))
				{
					$output[$index] = [];
				}
				$output[$index][$append_key] = $value;
			}
		}

		return $output;
	}
}

// --------------------------------------------------------------------------

if ( ! function_exists('regex_in_array'))
{
	/**
	 * Determine whether a value in the passed array matches the pattern
	 * passed
	 *
	 * @param array $array
	 * @param string $pattern
	 * @return bool
	 */
	function regex_in_array(array $array, string $pattern): bool
	{
		if (empty($array))
		{
			return FALSE;
		}

		foreach($array as $item)
		{
			if (is_scalar($item) && preg_match($pattern, $item))
			{
				return TRUE;
			}
		}

		return FALSE;
	}
}

// --------------------------------------------------------------------------

if ( ! function_exists('Query'))
{
	/**
	 * Connection function
	 *
	 * Send an array or object as connection parameters to create a connection. If
	 * the array or object has an 'alias' parameter, passing that string to this
	 * function will return that connection. Passing no parameters returns the last
	 * connection created.
	 *
	 * @param string|object|array $params
	 * @return Query\QueryBuilder|null
	 */
	function Query($params = '')
	{
		$manager = ConnectionManager::get_instance();

		// If you are getting a previously created connection
		if (is_scalar($params))
		{
			return $manager->get_connection($params);
		}
		elseif ( ! is_scalar($params) && ! is_null($params))
		{
			$params_object = (object) $params;

			// Otherwise, return a new connection
			return $manager->connect($params_object);
		}

		return NULL;
	}
}
// End of common.php