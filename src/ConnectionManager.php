<?php declare(strict_types=1);
/**
 * Query
 *
 * SQL Query Builder / Database Abstraction Layer
 *
 * PHP version 7.2
 *
 * @package     Query
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2012 - 2020 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link        https://git.timshomepage.net/aviat/Query
 * @version     3.0.0
 */
namespace Query;

use DomainException;

/**
 * Connection manager class to manage connections for the
 * Query method
 */
final class ConnectionManager {

	/**
	 * Map of named database connections
	 * @var array
	 */
	private array $connections = [];

	/**
	 * Class instance variable
	 * @var ConnectionManager|null
	 */
	private static $instance;

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
	public function __sleep(): void
	{
		throw new DomainException('No serializing of singleton');
	}

	/**
	 * Make sure serialize/deserialize doesn't work
	 *
	 * @throws DomainException
	 * @return void
	 */
	public function __wakeup(): void
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
	 * @throws Exception\NonExistentConnectionException
	 */
	public function getConnection($name = ''): QueryBuilderInterface
	{
		// If the parameter is a string, use it as an array index
		if (is_scalar($name) && isset($this->connections[$name]))
		{
			return $this->connections[$name];
		}

		if (empty($name) && ! empty($this->connections)) // Otherwise, return the last one
		{
			return end($this->connections);
		}

		// You should actually connect before trying to get a connection...
		throw new Exception\NonExistentConnectionException('The specified connection does not exist');
	}

	/**
	 * Parse the passed parameters and return a connection
	 *
	 * @param object|array $params
	 * @throws Exception\BadDBDriverException
	 * @return QueryBuilderInterface
	 */
	public function connect($params): QueryBuilderInterface
	{
		[$dsn, $dbType, $params, $options] = $this->parseParams($params);

		$dbType = ucfirst($dbType);
		$driver = "\\Query\\Drivers\\{$dbType}\\Driver";

		// Create the database connection
		$db =  ! empty($params->user)
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
	 * @param object | array $params
	 * @return array
	 * @throws Exception\BadDBDriverException
	 */
	public function parseParams($params): array
	{
		$params = (object) $params;
		$params->type = strtolower($params->type);
		$dbType = ($params->type === 'postgresql') ? 'pgsql' : $params->type;
		$dbType = ucfirst($dbType);

		// Make sure the class exists
		if ( ! class_exists("\\Query\\Drivers\\{$dbType}\\Driver"))
		{
			throw new Exception\BadDBDriverException('Database driver does not exist, or is not supported');
		}

		// Set additional PDO options
		$options = [];

		if (isset($params->options))
		{
			$options = (array) $params->options;
		}

		// Create the dsn for the database to connect to
		if(strtolower($dbType) === 'sqlite')
		{
			$dsn = $params->file;
		}
		else
		{
			$dsn = $this->createDsn($dbType, $params);
		}


		return [$dsn, $dbType, $params, $options];
	}

	/**
	 * Create the dsn from the db type and params
	 *
	 * @codeCoverageIgnore
	 * @param string $dbType
	 * @param array|object $params
	 * @return string
	 */
	private function createDsn(string $dbType, $params): string
	{
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
			if (( ! array_key_exists($key, $skip)) &&  ! empty($val))
			{
				$pairs[] = implode('=', [$key, $val]);
			}
		}

		return strtolower($dbType) . ':' . implode(';', $pairs);
	}
}