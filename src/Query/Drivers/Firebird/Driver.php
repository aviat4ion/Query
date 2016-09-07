<?php
/**
 * Query
 *
 * SQL Query Builder / Database Abstraction Layer
 *
 * PHP version 5.4
 *
 * @package     Query
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2012 - 2015 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link        https://git.timshomepage.net/aviat4ion/Query
 */

namespace Query\Drivers\Firebird;

use Query\Drivers\AbstractDriver;
use PDO;
use PDOException;

/**
 * Firebird Database class
 *
 * PDO-firebird isn't stable, so this is a wrapper of the fbird_ public functions.
 *
 * @package Query
 * @subpackage Drivers
 */
class Driver extends AbstractDriver {

	/**
	 * Reference to the last query executed
	 *
	 * @var object
	 */
	protected $statement = NULL;

	/**
	 * Reference to the resource returned by
	 * the last query executed
	 *
	 * @var resource
	 */
	protected $statement_link = NULL;

	/**
	 * Reference to the current transaction
	 *
	 * @var resource
	 */
	protected $trans = NULL;

	/**
	 * Reference to the connection resource
	 *
	 * @var resource
	 */
	protected $conn = NULL;

	/**
	 * Reference to the service resource
	 *
	 * @var resource
	 */
	protected $service = NULL;

	/**
	 * Firebird doesn't have the truncate keyword
	 *
	 * @var boolean
	 */
	protected $has_truncate = FALSE;

	/**
	 * Open the link to the database
	 *
	 * @param string $dbpath
	 * @param string $user
	 * @param string $pass
	 * @param array $options
	 * @throws \PDOException
	 */
	public function __construct($dbpath, $user='SYSDBA', $pass='masterkey', array $options = [])
	{
		$connect_function = (isset($options[PDO::ATTR_PERSISTENT]) && $options[PDO::ATTR_PERSISTENT])
			? '\\fbird_pconnect'
			: '\\fbird_connect';

		$this->conn = $connect_function($dbpath, $user, $pass, 'utf-8', 0);
		$this->service = \fbird_service_attach('localhost', $user, $pass);

		// Throw an exception to make this match other pdo classes
		if ( ! \is_resource($this->conn))
		{
			throw new PDOException(\fbird_errmsg(), \fbird_errcode(), NULL);
		}

		// Load these classes here because this
		// driver does not call the constructor
		// of AbstractDriver, which defines these
		// class variables for the other drivers
		$this->_load_sub_classes();
	}

	// --------------------------------------------------------------------------

	/**
	 * Cleanup some loose ends
	 * @codeCoverageIgnore
	 */
	public function __destruct()
	{
		\fbird_service_detach($this->service);
	}

	// --------------------------------------------------------------------------

	/**
	 * Return service handle
	 *
	 * @return resource
	 */
	public function get_service()
	{
		return $this->service;
	}


	// --------------------------------------------------------------------------

	/**
	 * Execute an sql statement and return number of affected rows
	 *
	 * @param string $sql
	 * @return int
	 */
	public function exec($sql)
	{
		return NULL;
	}

	// --------------------------------------------------------------------------

	/**
	 * Implement for compatibility with PDO
	 *
	 * @param int $attribute
	 * @return mixed
	 */
	public function getAttribute($attribute)
	{
		return NULL;
	}

	// --------------------------------------------------------------------------

	/**
	 * Return whether the current statement is in a transaction
	 *
	 * @return bool
	 */
	public function inTransaction()
	{
		return ! is_null($this->trans);
	}

	// --------------------------------------------------------------------------

	/**
	 * Returns the last value of the specified generator
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function lastInsertId($name = NULL)
	{
		return \fbird_gen_id($name, 0, $this->conn);
	}

	// --------------------------------------------------------------------------

	/**
	 * Wrapper public function to better match PDO
	 *
	 * @param string $sql
	 * @return Result
	 * @throws PDOException
	 */
	public function query($sql = '')
	{
		if (empty($sql))
		{
			throw new PDOException("Query method requires an sql query!", 0, NULL);
		}

		$this->statement_link = (isset($this->trans))
			? \fbird_query($this->trans, $sql)
			: \fbird_query($this->conn, $sql);

		// Throw the error as a exception
		$err_string = \fbird_errmsg() . "Last query:" . $this->get_last_query();
		if ($this->statement_link === FALSE)
		{
			throw new PDOException($err_string, \fbird_errcode(), NULL);
		}

		$this->statement = new Result($this->statement_link, $this);

		return $this->statement;
	}

	// --------------------------------------------------------------------------

	/**
	 * Emulate PDO prepare
	 *
	 * @param string $query
	 * @param array $options
	 * @return Result
	 * @throws PDOException
	 */
	public function prepare($query, $options=[])
	{
		$this->statement_link = \fbird_prepare($this->conn, $query);

		// Throw the error as an exception
		if ($this->statement_link === FALSE)
		{
			throw new PDOException(\fbird_errmsg(), \fbird_errcode(), NULL);
		}

		$this->statement = new Result($this->statement_link, $this);

		return $this->statement;
	}

	// --------------------------------------------------------------------------

	/**
	 * Start a database transaction
	 *
	 * @return boolean|null
	 */
	public function beginTransaction()
	{
		return (($this->trans = \fbird_trans($this->conn)) !== NULL) ? TRUE : NULL;
	}

	// --------------------------------------------------------------------------

	/**
	 * Commit a database transaction
	 *
	 * @return bool
	 */
	public function commit()
	{
		$res = \fbird_commit($this->trans);
		$this->trans = NULL;
		return $res;
	}

	// --------------------------------------------------------------------------

	/**
	 * Rollback a transaction
	 *
	 * @return bool
	 */
	public function rollBack()
	{
		$res = \fbird_rollback($this->trans);
		$this->trans = NULL;
		return $res;
	}

	// --------------------------------------------------------------------------

	/**
	 * Set a connection attribute
	 * @param int $attribute
	 * @param mixed $value
	 * @return bool
	 */
	public function setAttribute($attribute, $value)
	{
		return FALSE;
	}

	// --------------------------------------------------------------------------

	/**
	 * Prepare and execute a query
	 *
	 * @param string $sql
	 * @param array $args
	 * @return Result
	 */
	public function prepare_execute($sql, $args)
	{
		$query = $this->prepare($sql);

		// Set the statement in the class variable for easy later access
		$this->statement_link =& $query;

		return $query->execute($args);
	}

	// --------------------------------------------------------------------------

	/**
	 * Method to emulate PDO->quote
	 *
	 * @param string $str
	 * @param int $param_type
	 * @return string
	 */
	public function quote($str, $param_type = PDO::PARAM_STR)
	{
		if(is_numeric($str))
		{
			return $str;
		}

		return "'".str_replace("'", "''", $str)."'";
	}

	// --------------------------------------------------------------------------

	/**
	 * Method to emulate PDO->errorInfo / PDOStatement->errorInfo
	 *
	 * @return array
	 */
	public function errorInfo()
	{
		$code = \fbird_errcode();
		$msg = \fbird_errmsg();

		return [0, $code, $msg];
	}

	// --------------------------------------------------------------------------

	/**
	 * Method to emulate PDO->errorCode
	 *
	 * @return array
	 */
	public function errorCode()
	{
		return \fbird_errcode();
	}

	// --------------------------------------------------------------------------

	/**
	 * Bind a prepared query with arguments for executing
	 *
	 * @param string $sql
	 * @param array $params
	 * @return NULL
	 */
	public function prepare_query($sql, $params)
	{
		// You can't bind query statements before execution with
		// the firebird database
		return NULL;
	}

	// --------------------------------------------------------------------------

	/**
	 * Create sql for batch insert
	 *
	 * @param string $table
	 * @param array $data
	 * @return array
	 */
	public function insert_batch($table, $data=[])
	{
		// Each member of the data array needs to be an array
		if ( ! is_array(current($data)))
		{
			return NULL;
		}

		// Start the block of sql statements
		$sql = "EXECUTE BLOCK AS BEGIN\n";

		$table = $this->quote_table($table);
		$fields = \array_keys(\current($data));

		$insert_template = "INSERT INTO {$table} ("
			. implode(',', $this->quote_ident($fields))
			. ") VALUES (";

		foreach($data as $item)
		{
			// Quote string values
			$vals = array_map([$this, 'quote'], $item);

			// Add the values in the sql
			$sql .= $insert_template . implode(', ', $vals) . ");\n";
		}

		// End the block of SQL statements
		$sql .= "END";

		// Return a null array value so the query is run as it is,
		// not as a prepared statement, because a prepared statement
		// doesn't work for this type of query in Firebird.
		return [$sql, NULL];
	}
}
// End of firebird_driver.php