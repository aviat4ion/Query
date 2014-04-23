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

namespace Query;

/**
 * Generic exception for bad drivers
 *
 * @package Query
 * @subpackage Core
 */
class BadDBDriverException extends \InvalidArgumentException {}

// --------------------------------------------------------------------------

/**
 * Connection manager class to manage connections for the
 * Query method
 *
 * @package Query
 * @subpackage Core
 */
final class Connection_Manager {

	/**
	 * Map of named database connections
	 * @var array
	 */
	private $connections = array();

	/**
	 * Class instance variable
	 * @var Connection_Manager
	 */
	private static $instance = null;

	// --------------------------------------------------------------------------

	/**
	 * Private constructor to prevent multiple instances
	 * @codeCoverageIgnore
	 */
	private function __construct() {}

	// --------------------------------------------------------------------------

	/**
	 * Private clone method to prevent cloning
	 * @codeCoverageIgnore
	 */
	private function __clone() {}

	// --------------------------------------------------------------------------

	/**
	 * Make sure serialize/deserialize doesn't work
	 * @codeCoverageIgnore
	 * @throws \DomainException
	 */
	private function __wakeup()
	{
		throw new \DomainException("Can't unserialize singleton");
	}

	// --------------------------------------------------------------------------

	/**
	 * Return  a connection manager instance
	 *
	 * @staticvar null $instance
	 * @return Connection_Manager
	 */
	public static function get_instance()
	{

		// @codeCoverageIgnoreStart
		if (self::$instance === null)
		{
			self::$instance = new self();
		}
		// @codeCoverageIgnoreEnd

		return self::$instance;
	}

	// --------------------------------------------------------------------------

	/**
	 * Returns the connection specified by the name given
	 *
	 * @param string|array|object $name
	 * @return Query_Builder
	 * @throws \InvalidArgumentException
	 */
	public function get_connection($name = '')
	{
		// If the parameter is a string, use it as an array index
		if (is_scalar($name) && isset($this->connections[$name]))
		{
			return $this->connections[$name];
		}
		elseif (empty($name) && ! empty($this->connections)) // Otherwise, return the last one
		{
			return end($this->connections);
		}
		else
		{
			// You should actually connect before trying to get a connection...
			throw new \InvalidArgumentException("The specified connection does not exist");
		}
	}

	// --------------------------------------------------------------------------

	/**
	 * Parse the passed parameters and return a connection
	 *
	 * @param \ArrayObject $params
	 * @return Query_Builder
	 */
	public function connect(\ArrayObject $params)
	{
		list($dsn, $dbtype, $params, $options) = $this->parse_params($params);

		$driver = "\\Query\\Driver\\{$dbtype}";

		// Create the database connection
		$db = ( ! empty($params->user))
			? new $driver($dsn, $params->user, $params->pass, $options)
			: new $driver($dsn, '', '', $options);

		// Set the table prefix, if it exists
		if (isset($params->prefix))
		{
			$db->table_prefix = $params->prefix;
		}

		// Create Query Builder object
		$conn = new Query_Builder($db, new Query_Parser($db));


		// Save it for later
		if (isset($params->alias))
		{
			$this->connections[$params->alias] = $conn;
		}
		else
		{
			$this->connections[] = $conn;
		}

		return $conn;
	}

	// --------------------------------------------------------------------------

	/**
	 * Parses params into a dsn and option array
	 *
	 * @param \ArrayObject $params
	 * @return array
	 * @throws BadDBDriverException
	 */
	private function parse_params(\ArrayObject $params)
	{
		$params->type = strtolower($params->type);
		$dbtype = ($params->type !== 'postgresql') ? $params->type : 'pgsql';

		// Make sure the class exists
		if ( ! class_exists("Query\\Driver\\{$dbtype}"))
		{
			throw new BadDBDriverException('Database driver does not exist, or is not supported');
		}

		// Set additional PDO options
		$options = array();

		if (isset($params->options))
		{
			$options = (array) $params->options;
		}

		// Create the dsn for the database to connect to
		$dsn = $this->create_dsn($dbtype, $params);

		return array($dsn, $dbtype, $params, $options);
	}

	// --------------------------------------------------------------------------

	/**
	 * Create the dsn from the db type and params
	 *
	 * @param string $dbtype
	 * @param \ArrayObject $params
	 * @return string
	 */
	private function create_dsn($dbtype, \ArrayObject $params)
	{
		if ($dbtype === 'firebird') $dsn = "{$params->host}:{$params->file}";
		elseif ($dbtype === 'sqlite') $dsn = $params->file;
		else
		{
			$dsn = strtolower($dbtype) . ':';

			if ( ! empty($params->database))
			{
				$dsn .= "dbname={$params->database}";
			}

			$skip = array(
				'name' => 'name',
				'pass' => 'pass',
				'user' => 'user',
				'file' => 'file',
				'type' => 'type',
				'prefix' => 'prefix',
				'options' => 'options',
				'database' => 'database'
			);

			foreach($params as $key => $val)
			{
				if ( ! isset($skip[$key]))
				{
					$dsn .= ";{$key}={$val}";
				}
			}
		}

		return $dsn;
	}
}
// End of connection_manager.php
