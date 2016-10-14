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

namespace Query\Drivers;

use InvalidArgumentException;
use PDO;
use PDOStatement;

/**
 * Base Database class
 *
 * Extends PDO to simplify cross-database issues
 *
 * @package Query
 * @subpackage Drivers
 */
abstract class AbstractDriver extends PDO implements DriverInterface {

	/**
	 * Reference to the last executed query
	 * @var PDOStatement
	 */
	protected $statement;

	/**
	 * Start character to escape identifiers
	 * @var string
	 */
	protected $escapeCharOpen = '"';

	/**
	 * End character to escape identifiers
	 * @var string
	 */
	protected $escapeCharClose = '"';

	/**
	 * Reference to sql class
	 * @var SQLInterface
	 */
	protected $sql;

	/**
	 * Reference to util class
	 * @var AbstractUtil
	 */
	protected $util;

	/**
	 * Last query executed
	 * @var string
	 */
	protected $lastQuery = '';

	/**
	 * Prefix to apply to table names
	 * @var string
	 */
	protected $tablePrefix = '';

	/**
	 * Whether the driver supports 'TRUNCATE'
	 * @var boolean
	 */
	protected $hasTruncate = TRUE;

	/**
	 * PDO constructor wrapper
	 *
	 * @param string $dsn
	 * @param string $username
	 * @param string $password
	 * @param array $driverOptions
	 */
	public function __construct($dsn, $username=NULL, $password=NULL, array $driverOptions=[])
	{
		// Set PDO to display errors as exceptions, and apply driver options
		$driverOptions[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
		parent::__construct($dsn, $username, $password, $driverOptions);

		$this->_loadSubClasses();
	}

	/**
	 * Loads the subclasses for the driver
	 *
	 * @return void
	 */
	protected function _loadSubClasses()
	{
		// Load the sql and util class for the driver
		$thisClass = get_class($this);
		$nsArray = explode("\\", $thisClass);
		array_pop($nsArray);
		$driver = array_pop($nsArray);
		$sqlClass = __NAMESPACE__ . "\\{$driver}\\SQL";
		$utilClass = __NAMESPACE__ . "\\{$driver}\\Util";

		$this->sql = new $sqlClass();
		$this->util = new $utilClass($this);
	}

	/**
	 * Allow invoke to work on table object
	 *
	 * @codeCoverageIgnore
	 * @param string $name
	 * @param array $args
	 * @return mixed
	 */
	public function __call(string $name, array $args = [])
	{
		if (
			isset($this->$name)
			&& is_object($this->$name)
			&& method_exists($this->$name, '__invoke')
		)
		{
			return call_user_func_array([$this->$name, '__invoke'], $args);
		}
	}

	// --------------------------------------------------------------------------
	// ! Accessors / Mutators
	// --------------------------------------------------------------------------

	/**
	 * Get the last sql query executed
	 *
	 * @return string
	 */
	public function getLastQuery(): string
	{
		return $this->lastQuery;
	}

	/**
	 * Set the last query sql
	 *
	 * @param string $queryString
	 * @return void
	 */
	public function setLastQuery(string $queryString)
	{
		$this->lastQuery = $queryString;
	}

	/**
	 * Get the SQL class for the current driver
	 *
	 * @return SQLInterface
	 */
	public function getSql(): SQLInterface
	{
		return $this->sql;
	}

	/**
	 * Get the Util class for the current driver
	 *
	 * @return AbstractUtil
	 */
	public function getUtil(): AbstractUtil
	{
		return $this->util;
	}

	/**
	 * Set the common table name prefix
	 *
	 * @param string $prefix
	 * @return void
	 */
	public function setTablePrefix($prefix)
	{
		$this->tablePrefix = $prefix;
	}

	// --------------------------------------------------------------------------
	// ! Concrete functions that can be overridden in child classes
	// --------------------------------------------------------------------------

	/**
	 * Simplifies prepared statements for database queries
	 *
	 * @param string $sql
	 * @param array $data
	 * @return PDOStatement | FALSE
	 * @throws InvalidArgumentException
	 */
	public function prepareQuery($sql, $data)
	{
		// Prepare the sql, save the statement for easy access later
		$this->statement = $this->prepare($sql);

		if( ! (is_array($data) || is_object($data)))
		{
			throw new InvalidArgumentException("Data argument must be an object or associative array");
		}

		// Bind the parameters
		foreach($data as $k => $value)
		{
			// Parameters are 1-based, the data is 0-based
			// So, if the key is numeric, add 1
			if(is_numeric($k))
			{
				$k++;
			}
			$this->statement->bindValue($k, $value);
		}

		return $this->statement;
	}

	/**
	 * Create and execute a prepared statement with the provided parameters
	 *
	 * @param string $sql
	 * @param array $params
	 * @return PDOStatement
	 */
	public function prepareExecute($sql, $params)
	{
		$this->statement = $this->prepareQuery($sql, $params);
		$this->statement->execute();

		return $this->statement;
	}

	/**
	 * Returns number of rows affected by an INSERT, UPDATE, DELETE type query
	 *
	 * @return int
	 */
	public function affectedRows()
	{
		// Return number of rows affected
		return $this->statement->rowCount();
	}

	/**
	 * Prefixes a table if it is not already prefixed
	 * @param string $table
	 * @return string
	 */
	public function prefixTable($table)
	{
		// Add the prefix to the table name
		// before quoting it
		if ( ! empty($this->tablePrefix))
		{
			// Split identifier by period, will split into:
			// database.schema.table OR
			// schema.table OR
			// database.table OR
			// table
			$identifiers = explode('.', $table);
			$segments = count($identifiers);

			// Quote the last item, and add the database prefix
			$identifiers[$segments - 1] = $this->_prefix(end($identifiers));

			// Rejoin
			$table = implode('.', $identifiers);
		}

		return $table;
	}

	/**
	 * Quote database table name, and set prefix
	 *
	 * @param string $table
	 * @return string
	 */
	public function quoteTable($table)
	{
		$table = $this->prefixTable($table);

		// Finally, quote the table
		return $this->quoteIdent($table);
	}

	/**
	 * Surrounds the string with the databases identifier escape characters
	 *
	 * @param mixed $identifier
	 * @return string
	 */
	public function quoteIdent($identifier)
	{
		if (is_array($identifier))
		{
			return array_map([$this, __METHOD__], $identifier);
		}

		// Handle comma-separated identifiers
		if (strpos($identifier, ',') !== FALSE)
		{
			$parts = array_map('mb_trim', explode(',', $identifier));
			$parts = array_map([$this, __METHOD__], $parts);
			$identifier = implode(',', $parts);
		}

		// Split each identifier by the period
		$hiers = explode('.', $identifier);
		$hiers = array_map('mb_trim', $hiers);

		// Re-compile the string
		$raw = implode('.', array_map([$this, '_quote'], $hiers));

		// Fix functions
		$funcs = [];
		preg_match_all("#{$this->escapeCharOpen}([a-zA-Z0-9_]+(\((.*?)\))){$this->escapeCharClose}#iu", $raw, $funcs, PREG_SET_ORDER);
		foreach($funcs as $f)
		{
			// Unquote the function
			$raw = str_replace($f[0], $f[1], $raw);

			// Quote the inside identifiers
			$raw = str_replace($f[3], $this->quoteIdent($f[3]), $raw);
		}

		return $raw;

	}

	/**
	 * Return schemas for databases that list them
	 *
	 * @return array
	 */
	public function getSchemas()
	{
		return NULL;
	}

	/**
	 * Return list of tables for the current database
	 *
	 * @return array
	 */
	public function getTables()
	{
		$tables = $this->driverQuery('tableList');
		natsort($tables);
		return $tables;
	}

	/**
	 * Return list of dbs for the current connection, if possible
	 *
	 * @return array
	 */
	public function getDbs()
	{
		return $this->driverQuery('dbList');
	}

	/**
	 * Return list of views for the current database
	 *
	 * @return array
	 */
	public function getViews()
	{
		$views = $this->driverQuery('viewList');
		sort($views);
		return $views;
	}

	/**
	 * Return list of sequences for the current database, if they exist
	 *
	 * @return array
	 */
	public function getSequences()
	{
		return $this->driverQuery('sequenceList');
	}

	/**
	 * Return list of functions for the current database
	 *
	 * @return array
	 */
	public function getFunctions()
	{
		return $this->driverQuery('functionList', FALSE);
	}

	/**
	 * Return list of stored procedures for the current database
	 *
	 * @return array
	 */
	public function getProcedures()
	{
		return $this->driverQuery('procedureList', FALSE);
	}

	/**
	 * Return list of triggers for the current database
	 *
	 * @return array
	 */
	public function getTriggers()
	{
		return $this->driverQuery('triggerList', FALSE);
	}

	/**
	 * Retrieves an array of non-user-created tables for
	 * the connection/database
	 *
	 * @return array
	 */
	public function getSystemTables()
	{
		return $this->driverQuery('systemTableList');
	}

	/**
	 * Retrieve column information for the current database table
	 *
	 * @param string $table
	 * @return array
	 */
	public function getColumns($table)
	{
		return $this->driverQuery($this->getSql()->columnList($this->prefixTable($table)), FALSE);
	}

	/**
	 * Retrieve foreign keys for the table
	 *
	 * @param string $table
	 * @return array
	 */
	public function getFks($table)
	{
		return $this->driverQuery($this->getSql()->fkList($table), FALSE);
	}

	/**
	 * Retrieve indexes for the table
	 *
	 * @param string $table
	 * @return array
	 */
	public function getIndexes($table)
	{
		return $this->driverQuery($this->getSql()->indexList($this->prefixTable($table)), FALSE);
	}

	/**
	 * Retrieve list of data types for the database
	 *
	 * @return array
	 */
	public function getTypes()
	{
		return $this->driverQuery('typeList', FALSE);
	}

	/**
	 * Method to simplify retrieving db results for meta-data queries
	 *
	 * @param string|array|null $query
	 * @param bool $filteredIndex
	 * @return array
	 */
	public function driverQuery($query, $filteredIndex=TRUE)
	{
		// Call the appropriate method, if it exists
		if (is_string($query) && method_exists($this->sql, $query))
		{
			$query = $this->getSql()->$query();
		}

		// Return if the values are returned instead of a query,
		// or if the query doesn't apply to the driver
		if ( ! is_string($query))
		{
			return $query;
		}

		// Run the query!
		$res = $this->query($query);

		$flag = ($filteredIndex) ? PDO::FETCH_NUM : PDO::FETCH_ASSOC;
		$all = $res->fetchAll($flag);

		return ($filteredIndex) ? \db_filter($all, 0) : $all;
	}

	/**
	 * Return the number of rows returned for a SELECT query
	 *
	 * @see http://us3.php.net/manual/en/pdostatement.rowcount.php#87110
	 * @return int|null
	 */
	public function numRows()
	{
		$regex = '/^SELECT\s+(?:ALL\s+|DISTINCT\s+)?(?:.*?)\s+FROM\s+(.*)$/i';
		$output = [];

		if (preg_match($regex, $this->lastQuery, $output) > 0)
		{
			$stmt = $this->query("SELECT COUNT(*) FROM {$output[1]}");
			return (int) $stmt->fetchColumn();
		}

		return NULL;
	}

	/**
	 * Create sql for batch insert
	 *
	 * @param string $table
	 * @param array|object $data
	 * @return null|array<string|array|null>
	 */
	public function insertBatch($table, $data=[])
	{
		$data = (array) $data;
		$firstRow = (array) current($data);
		if (is_scalar($firstRow))
		{
			return NULL;
		}

		// Values for insertion
		$vals = [];
		foreach($data as $group)
		{
			$vals = array_merge($vals, array_values($group));
		}
		$table = $this->quoteTable($table);
		$fields = array_keys($firstRow);

		$sql = "INSERT INTO {$table} ("
			. implode(',', $this->quoteIdent($fields))
			. ") VALUES ";

		// Create the placeholder groups
		$params = array_fill(0, count($fields), '?');
		$paramString = "(" . implode(',', $params) . ")";
		$paramList = array_fill(0, count($data), $paramString);

		// Append the placeholder groups to the query
		$sql .= implode(',', $paramList);

		return [$sql, $vals];
	}

	/**
	 * Creates a batch update, and executes it.
	 * Returns the number of affected rows
	 *
	 * @param string $table
	 * @param array|object $data
	 * @param string $where
	 * @return int|null
	 */
	public function updateBatch($table, $data, $where)
	{
		// @TODO implement
		return NULL;
	}

	/**
	 * Helper method for quote_ident
	 *
	 * @param mixed $str
	 * @return mixed
	 */
	public function _quote($str)
	{
		// Check that the current value is a string,
		// and is not already quoted before quoting
		// that value, otherwise, return the original value
		return (
			is_string($str)
			&& strpos($str, $this->escapeCharOpen) !== 0
			&& strrpos($str, $this->escapeCharClose) !== 0
		)
			? "{$this->escapeCharOpen}{$str}{$this->escapeCharClose}"
			: $str;

	}

	/**
	 * Sets the table prefix on the passed string
	 *
	 * @param string $str
	 * @return string
	 */
	protected function _prefix($str)
	{
		// Don't prefix an already prefixed table
		if (strpos($str, $this->tablePrefix) !== FALSE)
		{
			return $str;
		}

		return $this->tablePrefix . $str;
	}

	/**
	 * Empty the passed table
	 *
	 * @param string $table
	 * @return PDOStatement
	 */
	public function truncate($table)
	{
		$sql = ($this->hasTruncate)
			? 'TRUNCATE TABLE '
			: 'DELETE FROM ';

		$sql .= $this->quoteTable($table);

		$this->statement = $this->query($sql);
		return $this->statement;
	}

}