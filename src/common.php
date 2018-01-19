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


use Query\{
    ConnectionManager,
    QueryBuilderInterface
};

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
		return preg_replace('/(^\s+)|(\s+$)/u', '', $string);
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
		$newArray = [];

		foreach($array as $a)
		{
			$newArray[] = $a[$index];
		}

		return $newArray;
	}
}

if ( ! function_exists('to_camel_case'))
{
	/**
	 * Create a camelCase string from snake_case
	 *
	 * @param string $snakeCase
	 * @return string
	 */
	function to_camel_case(string $snakeCase): string
	{
		$pieces = explode('_', $snakeCase);
		$numPieces = count($pieces);

		$pieces[0] = mb_strtolower($pieces[0]);
		for($i = 1; $i < $numPieces; $i++)
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
	 * The $zipperInput array is an array of arrays indexed by their place in the output
	 * array.
	 *
	 * @param array $zipperInput
	 * @return array
	 */
	function array_zipper(array $zipperInput): array
	{
		$output = [];

		foreach($zipperInput as $appendKey => $values)
		{
			foreach($values as $index => $value)
			{
				if ( ! isset($output[$index]))
				{
					$output[$index] = [];
				}
				$output[$index][$appendKey] = $value;
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
	 * @return QueryBuilderInterface|null
	 */
	function Query($params = ''): ?QueryBuilderInterface
	{
		$manager = ConnectionManager::getInstance();

		if ($params === NULL)
        {
            return NULL;
        }

		// If you are getting a previously created connection
		if (is_scalar($params))
		{
			return $manager->getConnection($params);
		}

        $paramsObject = (object) $params;

        // Otherwise, return a new connection
        return $manager->connect($paramsObject);
	}
}
// End of common.php
