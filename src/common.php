<?php declare(strict_types=1);
/**
 * Query
 *
 * SQL Query Builder / Database Abstraction Layer
 *
 * PHP version 7.4
 *
 * @package     Query
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2012 - 2020 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link        https://git.timshomepage.net/aviat/Query
 * @version     3.0.0
 */
namespace {

	use Query\ConnectionManager;
	use Query\QueryBuilderInterface;

	/**
	 * Global functions that don't really fit anywhere else
	 */

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

	/**
	 * Filter out db rows into one array
	 *
	 * @param array $array
	 * @param mixed $index
	 * @return array
	 */
	function dbFilter(array $array, $index): array
	{
		$newArray = [];

		foreach ($array as $a)
		{
			$newArray[] = $a[$index];
		}

		return $newArray;
	}

	/**
	 * Zip a set of arrays together on common keys
	 *
	 * The $zipperInput array is an array of arrays indexed by their place in the output
	 * array.
	 *
	 * @param array $zipperInput
	 * @return array
	 */
	function arrayZipper(array $zipperInput): array
	{
		$output = [];

		foreach ($zipperInput as $appendKey => $values)
		{
			foreach ($values as $index => $value)
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

	/**
	 * Determine whether a value in the passed array matches the pattern
	 * passed
	 *
	 * @param array $array
	 * @param string $pattern
	 * @return bool
	 */
	function regexInArray(array $array, string $pattern): bool
	{
		if (empty($array))
		{
			return FALSE;
		}

		foreach ($array as $item)
		{
			if (is_scalar($item) && preg_match($pattern, $item))
			{
				return TRUE;
			}
		}

		return FALSE;
	}

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
		if ($params === NULL)
		{
			return NULL;
		}

		$manager = ConnectionManager::getInstance();

		// If you are getting a previously created connection
		if (is_scalar($params))
		{
			return $manager->getConnection($params);
		}

		$paramsObject = (object)$params;

		// Otherwise, return a new connection
		return $manager->connect($paramsObject);
	}
}
// End of common.php
