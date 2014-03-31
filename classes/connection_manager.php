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
 * Generic exception for bad drivers
 *
 * @package Query
 * @subpackage Query
 */
class BadDBDriverException extends InvalidArgumentException {}

// --------------------------------------------------------------------------

/**
 * Connection manager class to manage connections for the
 * Query method
 *
 * @package Query
 * @subpackage Query
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

	/**
	 * Private methods for singleton
	 */
	private function __construct() {}
	private function __clone() {}

	/**
	 * Make sure serialize/deseriaze doesn't work
	 * @throws DomainException
	 */
	private function __wakup()
	{
		throw new DomainException("Can't unserialize singleton");
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

		if (self::$instance === null)
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	// --------------------------------------------------------------------------

	/**
	 * Returns the connection specified by the name given
	 *
	 * @param mixed $name
	 * @return Query_Builder
	 * @throws InvalidArgumentException
	 */
	public function get_connection($name = '')
	{
		// If the paramater is a string, use it as an array index
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
			throw new InvalidArgumentException("The specified connection does not exist");
		}
	}

	// --------------------------------------------------------------------------

	/**
	 * Parse the passed parameters and return a connection
	 *
	 * @param array|object $params
	 * @return Query_Builder
	 * @throws BadConnectionException
	 */
	public function connect($params)
	{
		list($dsn, $dbtype, $params, $options) = $this->parse_params($params);

		// Create the database connection
		$db = ( ! empty($params->user))
			? new $dbtype($dsn, $params->user, $params->pass, $options)
			: new $dbtype($dsn, '', '', $options);

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
	 * @param ArrayObject $params
	 * @throws BadDBDriverException
	 */
	private function parse_params($params)
	{
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

		// Make sure the class exists
		if ( ! class_exists($dbtype))
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
	 * @param array|object $params
	 * @return string
	 */
	private function create_dsn($dbtype, $params)
	{
		// Add the driver type to the dsn
		$dsn = ($dbtype !== 'firebird' && $dbtype !== 'sqlite')
			? strtolower($dbtype).':'
			: '';

		if ($dbtype === 'firebird') $dsn = "{$params->host}:{$params->file}";
		elseif ($dbtype === 'sqlite') $dsn = $params->file;
		else
		{
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
