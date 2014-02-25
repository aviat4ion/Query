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
 * @param mixed $params
 * @return Query_Builder
 * @throws InvalidArgumentException
 * @throws BadDBDriverException
 * @throws BadConnectionException
 */
function Query($params = '')
{
	static $connections;

	// If you are getting a previously created connection
	if (is_scalar($params))
	{
		// If the paramater is a string, use it as an array index
		if (is_scalar($params) && isset($connections[$params]))
		{
			return $connections[$params];
		}
		elseif (empty($params) && ! empty($connections)) // Otherwise, return the last one
		{
			return end($connections);
		}

		throw new InvalidArgumentException("The specified connection does not exist");
	}

	// --------------------------------------------------------------------------
	// Parse argument array / object
	// --------------------------------------------------------------------------

	// Convert array to object
	if (is_array($params))
	{
		$params = new ArrayObject($params, ArrayObject::STD_PROP_LIST | ArrayObject::ARRAY_AS_PROPS);
	}

	$params->type = strtolower($params->type);
	$dbtype = ($params->type !== 'postgresql') ? $params->type : 'pgsql';

	// Let the connection work with 'conn_db' or 'database'
	if (isset($params->database))
	{
		$params->conn_db = $params->database;
	}

	// Add the driver type to the dsn
	$dsn = ($dbtype !== 'firebird' && $dbtype !== 'sqlite')
		? strtolower($dbtype).':'
		: '';

	// Make sure the class exists
	if ( ! class_exists($dbtype))
	{
		throw new BadDBDriverException('Database driver does not exist, or is not supported');
	}

	// Set additional PDO options
	$options = array();

	if (isset($params->options))
	{
		$options = (array)$params->options;
	}

	// --------------------------------------------------------------------------
	// Attempt to connect
	// --------------------------------------------------------------------------

	// Create the dsn for the database to connect to
	if ($dbtype === 'firebird') $dsn = "{$params->host}:{$params->file}";
	elseif ($dbtype === 'sqlite') $dsn = $params->file;
	else
	{
		if ( ! empty($params->conn_db))
		{
			$dsn .= "dbname={$params->conn_db}";
		}

		if ( ! empty($params->host))
		{
			$dsn .= ";host={$params->host}";
		}

		if ( ! empty($params->port))
		{
			$dsn .= ";port={$params->port}";
		}
	}

	try
	{
		// Create the database connection
		$db = ( ! empty($params->user))
			? new $dbtype($dsn, $params->user, $params->pass, $options)
			: new $dbtype($dsn, '', '', $options);
	}
	catch(Exception $e)
	{
		throw new BadConnectionException('Connection failed, invalid arguments', 2);
	}

	// --------------------------------------------------------------------------
	// Save connection
	// --------------------------------------------------------------------------

	// Set the table prefix, if it exists
	if (isset($params->prefix))
	{
		$db->table_prefix = $params->prefix;
	}

	// Create the Query Builder object
	$conn = new Query_Builder($db, $params);

	// Save it for later
	if (isset($params->alias))
	{
		$connections[$params->alias] = $conn;
	}
	else
	{
		$connections[] = $conn;
	}

	// Return the Query Builder object
	return $conn;
}

// End of common.php