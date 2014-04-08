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

namespace Query\Driver;

use Query\Table\Table_Builder;

// --------------------------------------------------------------------------

/**
 * Base Database class
 *
 * Extends PDO to simplify cross-database issues
 *
 * @package Query
 * @subpackage Drivers
 */
abstract class Abstract_Driver extends \PDO implements Driver_Interface {

	/**
	 * Reference to the last executed query
	 * @var PDOStatement
	 */
	protected $statement;

	/**
	 * Character to escape indentifiers
	 * @var string
	 */
	protected $escape_char = '"';

	/**
	 * Reference to sql class
	 * @var SQL_Interface
	 */
	public $sql;

	/**
	 * Reference to util class
	 * @var DB_Util
	 */
	public $util;

	/**
	 * Reference to table_builder class
	 * @var \Query\Table\Table_Builder
	 */
	public $table;

	/**
	 * Last query executed
	 * @var string
	 */
	public $last_query;

	/**
	 * Prefix to apply to table names
	 * @var string
	 */
	public $table_prefix = '';

	/**
	 * PDO constructor wrapper
	 *
	 * @param string $dsn
	 * @param string $username
	 * @param string $password
	 * @param array $driver_options
	 */
	public function __construct($dsn, $username=NULL, $password=NULL, array $driver_options=array())
	{
		// Set PDO to display errors as exceptions, and apply driver options
		$driver_options[\PDO::ATTR_ERRMODE] = \PDO::ERRMODE_EXCEPTION;
		parent::__construct($dsn, $username, $password, $driver_options);

		// Load the sql and util class for the driver
		foreach(array('sql', 'util') as $sub)
		{
			$class = get_class($this) . "_{$sub}";
			$this->$sub = new $class($this);
		}

		$this->table = new Table_Builder('', array(), $this);
	}

	// --------------------------------------------------------------------------
	// ! Concrete functions that can be overridden in child classes
	// --------------------------------------------------------------------------

	/**
	 * Simplifies prepared statements for database queries
	 *
	 * @param string $sql
	 * @param array $data
	 * @return \PDOStatement | FALSE
	 * @throws \InvalidArgumentException
	 */
	public function prepare_query($sql, $data)
	{
		// Prepare the sql, save the statement for easy access later
		$this->statement = $this->prepare($sql);

		if( ! (is_array($data) || is_object($data)))
		{
			throw new \InvalidArgumentException("Invalid data argument");
		}

		// Bind the parameters
		foreach($data as $k => $value)
		{
			// Parameters are 1-based, the data is 0-based
			// So, if the key is numeric, add 1
			if(is_numeric($k)) $k++;
			$this->statement->bindValue($k, $value);
		}

		return $this->statement;
	}

	// -------------------------------------------------------------------------

	/**
	 * Create and execute a prepared statement with the provided parameters
	 *
	 * @param string $sql
	 * @param array $params
	 * @return \PDOStatement
	 */
	public function prepare_execute($sql, $params)
	{
		$this->statement = $this->prepare_query($sql, $params);
		$this->statement->execute();

		return $this->statement;
	}

	// -------------------------------------------------------------------------

	/**
	 * Returns number of rows affected by an INSERT, UPDATE, DELETE type query
	 *
	 * @return int
	 */
	public function affected_rows()
	{
		// Return number of rows affected
		return $this->statement->rowCount();
	}

	// --------------------------------------------------------------------------

	/**
	 * Quote database table name, and set prefix
	 *
	 * @param string $table
	 * @return string
	 */
	public function quote_table($table)
	{
		// Add the prefix to the table name
		// before quoting it
		if ( ! empty($this->table_prefix))
		{
			// Split indentifier by period, will split into:
			// database.schema.table OR
			// schema.table OR
			// database.table OR
			// table
			$idents = explode('.', $table);
			$segments = count($idents);

			// Quote the last item, and add the database prefix
			$idents[$segments - 1] = $this->_prefix(end($idents));

			// Rejoin
			$table = implode('.', $idents);
		}

		// Finally, quote the table
		return $this->quote_ident($table);
	}

	// --------------------------------------------------------------------------

	/**
	 * Sets the table prefix on the passed string
	 *
	 * @param string $str
	 * @return string
	 */
	protected function _prefix($str)
	{
		// Don't prefix an already prefixed table
		if (strpos($str, $this->table_prefix) !== FALSE)
		{
			return $str;
		}

		return $this->table_prefix.$str;
	}

	// --------------------------------------------------------------------------

	/**
	 * Surrounds the string with the databases identifier escape characters
	 *
	 * @param mixed $ident
	 * @return string
	 */
	public function quote_ident($ident)
	{
		if (is_array($ident))
		{
			return array_map(array($this, __METHOD__), $ident);
		}

		// Handle comma-separated identifiers
		if (strpos($ident, ',') !== FALSE)
		{
			$parts = explode(',', $ident);
			$parts = array_map('mb_trim', $parts);
			$parts = array_map(array($this, __METHOD__), $parts);
			$ident = implode(',', $parts);
		}

		// Split each identifier by the period
		$hiers = explode('.', $ident);
		$hiers = array_map('mb_trim', $hiers);

		// Re-compile the string
		$raw = implode('.', array_map(array($this, '_quote'), $hiers));

		// Fix functions
		$funcs = array();
		preg_match_all("#{$this->escape_char}([a-zA-Z0-9_]+(\((.*?)\))){$this->escape_char}#iu", $raw, $funcs, PREG_SET_ORDER);
		foreach($funcs as $f)
		{
			// Unquote the function
			$raw = str_replace($f[0], $f[1], $raw);

			// Quote the inside identifiers
			$raw = str_replace($f[3], $this->quote_ident($f[3]), $raw);
		}

		return $raw;

	}

	// --------------------------------------------------------------------------

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
			strpos($str, $this->escape_char) !== 0
			&& strrpos($str, $this->escape_char) !== 0
			&& is_string($str)
			&& ! is_numeric($str)
		)
			? "{$this->escape_char}{$str}{$this->escape_char}"
			: $str;

	}

	// -------------------------------------------------------------------------

	/**
	 * Return schemas for databases that list them
	 *
	 * @return array
	 */
	public function get_schemas()
	{
		return NULL;
	}

	// -------------------------------------------------------------------------

	/**
	 * Return list of tables for the current database
	 *
	 * @return array
	 */
	public function get_tables()
	{
		return $this->driver_query('table_list');
	}

	// -------------------------------------------------------------------------

	/**
	 * Return list of dbs for the current connection, if possible
	 *
	 * @return array
	 */
	public function get_dbs()
	{
		return $this->driver_query('db_list');
	}

	// -------------------------------------------------------------------------

	/**
	 * Return list of views for the current database
	 *
	 * @return array
	 */
	public function get_views()
	{
		return $this->driver_query('view_list');
	}

	// -------------------------------------------------------------------------

	/**
	 * Return list of sequences for the current database, if they exist
	 *
	 * @return array
	 */
	public function get_sequences()
	{
		return $this->driver_query('sequence_list');
	}

	// -------------------------------------------------------------------------

	/**
	 * Return list of function for the current database
	 *
	 * @return array
	 */
	public function get_functions()
	{
		return $this->driver_query('function_list', FALSE);
	}

	// -------------------------------------------------------------------------

	/**
	 * Return list of stored procedures for the current database
	 *
	 * @return array
	 */
	public function get_procedures()
	{
		return $this->driver_query('procedure_list', FALSE);
	}

	// -------------------------------------------------------------------------

	/**
	 * Return list of triggers for the current database
	 *
	 * @return array
	 */
	public function get_triggers()
	{
		return $this->driver_query('trigger_list', FALSE);
	}

	// -------------------------------------------------------------------------

	/**
	 * Retreives an array of non-user-created tables for
	 * the connection/database
	 *
	 * @return array
	 */
	public function get_system_tables()
	{
		return $this->driver_query('system_table_list');
	}

	// --------------------------------------------------------------------------

	/**
	 * Retrieve column information for the current database table
	 *
	 * @param string $table
	 * @return array
	 */
	public function get_columns($table)
	{
		return $this->driver_query($this->sql->column_list($table), FALSE);
	}

	// --------------------------------------------------------------------------

	/**
	 * Retrieve foreign keys for the table
	 *
	 * @param string $table
	 * @return array
	 */
	public function get_fks($table)
	{
		return $this->driver_query($this->sql->fk_list($table), FALSE);
	}

	// --------------------------------------------------------------------------

	/**
	 * Retrieve indexes for the table
	 *
	 * @param string $table
	 * @return array
	 */
	public function get_indexes($table)
	{
		return $this->driver_query($this->sql->index_list($table), FALSE);
	}

	// --------------------------------------------------------------------------

	/**
	 * Retrieve list of data types for the database
	 *
	 * @return array
	 */
	public function get_types()
	{
		return $this->driver_query('type_list', FALSE);
	}

	// -------------------------------------------------------------------------

	/**
	 * Method to simplify retreiving db results for meta-data queries
	 *
	 * @param string|array|null $query
	 * @param bool $filtered_index
	 * @return array
	 */
	public function driver_query($query, $filtered_index=TRUE)
	{
		// Call the appropriate method, if it exists
		if (is_string($query) && method_exists($this->sql, $query))
		{
			$query = $this->sql->$query();
		}

		// Return if the values are returned instead of a query,
		// or if the query doesn't apply to the driver
		if ( ! is_string($query)) return $query;

		// Run the query!
		$res = $this->query($query);

		$flag = ($filtered_index) ? \PDO::FETCH_NUM : \PDO::FETCH_ASSOC;
		$all = $res->fetchAll($flag);

		return ($filtered_index) ? \db_filter($all, 0) : $all;
	}

	// --------------------------------------------------------------------------

	/**
	 * Return the number of rows returned for a SELECT query
	 *
	 * @see http://us3.php.net/manual/en/pdostatement.rowcount.php#87110
	 * @return int
	 */
	public function num_rows()
	{
		$regex = '/^SELECT\s+(?:ALL\s+|DISTINCT\s+)?(?:.*?)\s+FROM\s+(.*)$/i';
		$output = array();

		if (preg_match($regex, $this->last_query, $output) > 0)
		{
			$stmt = $this->query("SELECT COUNT(*) FROM {$output[1]}");
			return (int) $stmt->fetchColumn();
		}

		return NULL;
	}

	// -------------------------------------------------------------------------
	// ! Abstract public functions to implement in child classes
	// -------------------------------------------------------------------------

	/**
	 * Empty the passed table
	 *
	 * @param string $table
	 * @return void
	 */
	abstract public function truncate($table);

	// --------------------------------------------------------------------------

	/**
	 * Create sql for batch insert
	 *
	 * @param string $table
	 * @param array $data
	 * @return array
	 */
	public function insert_batch($table, $data=array())
	{
		if ( ! is_array(current($data))) return NULL;

		$vals = array(); // Values for insertion
		$table = $this->quote_table($table);
		$fields = array_keys(current($data));

		$sql = "INSERT INTO {$table} ("
			. implode(',', $this->quote_ident($fields))
			. ") VALUES ";

		// Create the placeholder groups
		$params = array_fill(0, count($fields), '?');
		$param_string = "(" . implode(',', $params) . ")";
		$param_list = array_fill(0, count($data), $param_string);

		// For each grouping, add the values
		foreach($data as $group)
		{
			$vals = array_merge($vals, array_values($group));
		}

		// Add the param groups
		$sql .= implode(',', $param_list);

		return array($sql, $vals);
	}
}
// End of db_pdo.php