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
	 * @throws \DomainException
	 */
	public function __clone()
	{
		throw new \DomainException("Can't clone singleton");
	}

	// --------------------------------------------------------------------------

	/**
	 * Prevent serialization of this object
	 * @throws \DomainException
	 */
	public function __sleep()
	{
		throw new \DomainException("No serializing of singleton");
	}

	// --------------------------------------------------------------------------

	/**
	 * Make sure serialize/deserialize doesn't work
	 * @throws \DomainException
	 */
	public function __wakeup()
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
		if (self::$instance === null) self::$instance = new self();

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

		// You should actually connect before trying to get a connection...
		throw new \InvalidArgumentException("The specified connection does not exist");
	}

	// --------------------------------------------------------------------------

	/**
	 * Parse the passed parameters and return a connection
	 *
	 * @param \stdClass $params
	 * @return Query_Builder
	 */
	public function connect(\stdClass $params)
	{
		list($dsn, $dbtype, $params, $options) = $this->parse_params($params);

		$dbtype = ucfirst($dbtype);
		$driver = "\\Query\\Drivers\\{$dbtype}\\Driver";

		// Create the database connection
		$db = ( ! empty($params->user))
			? new $driver($dsn, $params->user, $params->pass, $options)
			: new $driver($dsn, '', '', $options);

		// Set the table prefix, if it exists
		if (isset($params->prefix))
		{
			$db->set_table_prefix($params->prefix);
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
	 * @param \stdClass $params
	 * @return array
	 * @throws BadDBDriverException
	 */
	public function parse_params(\stdClass $params)
	{
		$params->type = strtolower($params->type);
		$dbtype = ($params->type !== 'postgresql') ? $params->type : 'pgsql';
		$dbtype = ucfirst($dbtype);

		// Make sure the class exists
		if ( ! class_exists("\\Query\\Drivers\\{$dbtype}\\Driver"))
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
		if (strtolower($dbtype) === 'firebird')
		{
			$dsn = "{$params->host}:{$params->file}";
		}
		else if(strtolower($dbtype) === 'sqlite')
		{
			$dsn = $params->file;
		}
		else
		{
			$dsn = $this->create_dsn($dbtype, $params);
		}


		return array($dsn, $dbtype, $params, $options);
	}

	// --------------------------------------------------------------------------

	/**
	 * Create the dsn from the db type and params
	 *
	 * @param string $dbtype
	 * @param \stdClass $params
	 * @return string
	 */
	private function create_dsn($dbtype, \stdClass $params)
	{
		if (strtolower($dbtype) === 'pdo_firebird') $dbtype = 'firebird';

		$pairs = array();

		if ( ! empty($params->database))
		{
			$pairs[] = implode('=', array('dbname', $params->database));
		}

		$skip = array(
			'name' => 'name',
			'pass' => 'pass',
			'user' => 'user',
			'type' => 'type',
			'prefix' => 'prefix',
			'options' => 'options',
			'database' => 'database',
			'alias' => 'alias'
		);

		foreach($params as $key => $val)
		{
			if (( ! array_key_exists($key, $skip)) && ( ! empty($val)))
			{
				$pairs[] = implode('=', array($key, $val));
			}
		}

		return strtolower($dbtype) . ':' . implode(';', $pairs);
	}
}
// End of connection_manager.php