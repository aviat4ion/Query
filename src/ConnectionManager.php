<?php declare(strict_types=1);
/**
 * Query
 *
 * SQL Query Builder / Database Abstraction Layer
 *
 * PHP version 7.1
 *
 * @package     Query
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2012 - 2018 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link        https://git.timshomepage.net/aviat4ion/Query
 */
namespace Query;

use DomainException;
use InvalidArgumentException;

/**
 * Connection manager class to manage connections for the
 * Query method
 */
final class ConnectionManager {

	/**
	 * Map of named database connections
	 * @var array
	 */
	private $connections = [];

	/**
	 * Class instance variable
	 * @var ConnectionManager
	 */
	private static $instance = NULL;

	/**
	 * Private constructor to prevent multiple instances
	 * @codeCoverageIgnore
	 */
	private function __construct()
	{
	}

	/**
	 * Private clone method to prevent cloning
	 *
	 * @throws DomainException
	 * @return void
	 */
	public function __clone()
	{
		throw new DomainException("Can't clone singleton");
	}

	/**
	 * Prevent serialization of this object
	 *
	 * @throws DomainException
	 * @return void
	 */
	public function __sleep()
	{
		throw new DomainException("No serializing of singleton");
	}

	/**
	 * Make sure serialize/deserialize doesn't work
	 *
	 * @throws DomainException
	 * @return void
	 */
	public function __wakeup()
	{
		throw new DomainException("Can't unserialize singleton");
	}

	/**
	 * Return  a connection manager instance
	 *
	 * @staticvar null $instance
	 * @return ConnectionManager
	 */
	public static function getInstance(): ConnectionManager
	{
		if (self::$instance === NULL)
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Returns the connection specified by the name given
	 *
	 * @param string|array|object $name
	 * @return QueryBuilderInterface
	 * @throws InvalidArgumentException
	 */
	public function getConnection($name = ''): QueryBuilderInterface
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
		throw new InvalidArgumentException("The specified connection does not exist");
	}

	/**
	 * Parse the passed parameters and return a connection
	 *
	 * @param \stdClass $params
	 * @return QueryBuilderInterface
	 */
	public function connect(\stdClass $params): QueryBuilderInterface
	{
		list($dsn, $dbtype, $params, $options) = $this->parseParams($params);

		$dbtype = ucfirst($dbtype);
		$driver = "\\Query\\Drivers\\{$dbtype}\\Driver";

		// Create the database connection
		$db = ( ! empty($params->user))
			? new $driver($dsn, $params->user, $params->pass, $options)
			: new $driver($dsn, '', '', $options);

		// Set the table prefix, if it exists
		if (isset($params->prefix))
		{
			$db->setTablePrefix($params->prefix);
		}

		// Create Query Builder object
		$conn = new QueryBuilder($db, new QueryParser($db));


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

	/**
	 * Parses params into a dsn and option array
	 *
	 * @param \stdClass $params
	 * @return array
	 * @throws BadDBDriverException
	 */
	public function parseParams(\stdClass $params): array
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
		$options = [];

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
		else if(strtolower($dbtype) === 'oci')
		{
			$dsn = "dbname=//{$params->host}:{$params->port}/{$params->database}";
		}
		else
		{
			$dsn = $this->createDsn($dbtype, $params);
		}


		return [$dsn, $dbtype, $params, $options];
	}

	/**
	 * Create the dsn from the db type and params
	 *
	 * @param string $dbtype
	 * @param \stdClass $params
	 * @return string
	 */
	private function createDsn(string $dbtype, \stdClass $params): string
	{
		if (strtolower($dbtype) === 'pdo_firebird')
		{
			$dbtype = 'firebird';
		}

		$pairs = [];

		if ( ! empty($params->database))
		{
			$pairs[] = implode('=', ['dbname', $params->database]);
		}

		$skip = [
			'name' => 'name',
			'pass' => 'pass',
			'user' => 'user',
			'type' => 'type',
			'prefix' => 'prefix',
			'options' => 'options',
			'database' => 'database',
			'alias' => 'alias'
		];

		foreach($params as $key => $val)
		{
			if (( ! array_key_exists($key, $skip)) && ( ! empty($val)))
			{
				$pairs[] = implode('=', [$key, $val]);
			}
		}

		return strtolower($dbtype) . ':' . implode(';', $pairs);
	}
}