<?php
/**
 * Query
 *
 * Free Query Builder / Database Abstraction Layer
 *
 * @author 		Timothy J. Warren
 * @copyright	Copyright (c) 2012
 * @link 		https://github.com/aviat4ion/Query
 * @license 	http://philsturgeon.co.uk/code/dbad-license 
 */

// --------------------------------------------------------------------------

/**
 * Firebird Database class
 *
 * PDO-firebird isn't stable, so this is a wrapper of the fbird_ public functions.
 */
class Firebird extends DB_PDO {

	protected $statement, 
		$statement_link, 
		$trans, 
		$count, 
		$result, 
		$conn;

	/**
	 * Open the link to the database
	 *
	 * @param string $db
	 * @param string $user
	 * @param string $pass
	 */
	public function __construct($dbpath, $user='sysdba', $pass='masterkey')
	{
		$this->conn = fbird_connect($dbpath, $user, $pass, 'utf-8');

		// Throw an exception to make this match other pdo classes
		if ( ! is_resource($this->conn))
		{
			throw new PDOException(fbird_errmsg());
			die();
		}
		
		// Load these classes here because this
		// driver does not call the constructor
		// of DB_PDO, which defines these two 
		// class variables for the other drivers
		
		// Load the sql class
		$class = __CLASS__."_sql";
		$this->sql = new $class();
		
		// Load the util class
		$class = __CLASS__."_util";
		$this->util = new $class($this);
	}

	// --------------------------------------------------------------------------

	/**
	 * Doesn't apply to Firebird
	 */
	public function switch_db($name)
	{
		return FALSE;
	}

	// --------------------------------------------------------------------------

	/**
	 * Empty a database table
	 *
	 * @param string $table
	 */
	public function truncate($table)
	{
		// Firebird lacka a truncate command
		$sql = 'DELETE FROM "'.$table.'"';
		$this->statement = $this->query($sql);
	}

	// --------------------------------------------------------------------------

	/**
	 * Wrapper public function to better match PDO
	 *
	 * @param string $sql
	 * @return $this
	 */
	public function query($sql)
	{
		$this->count = 0;

		$this->statement_link = (isset($this->trans))
			? fbird_query($this->trans, $sql)
			: fbird_query($this->conn, $sql);

		// Throw the error as a exception
		if ($this->statement_link === FALSE)
		{
			throw new PDOException(fbird_errmsg());
		}

		return new FireBird_Result($this->statement_link);
	}

	// --------------------------------------------------------------------------

	/**
	 * Emulate PDO prepare
	 *
	 * @param string $query
	 * @return $this
	 */
	public function prepare($query, $options=NULL)
	{
		$this->statement_link = fbird_prepare($this->conn, $query);

		// Throw the error as an exception
		if ($this->statement_link === FALSE)
		{
			throw new PDOException(fbird_errmsg());
		}

		return new FireBird_Result($this->statement_link);
	}

	// --------------------------------------------------------------------------

	/**
	 * Return the number of rows returned for a SELECT query
	 *
	 * @return int
	 */
	public function num_rows()
	{
		return $this->statement->num_rows();
	}

	// --------------------------------------------------------------------------

	/**
	 * Start a database transaction
	 *
	 * @return bool
	 */
	public function beginTransaction()
	{
		if(($this->trans = fbird_trans($this->conn)) !== NULL)
		{
			return TRUE;
		}

		return FALSE;
	}

	// --------------------------------------------------------------------------

	/**
	 * Commit a database transaction
	 *
	 * @return bool
	 */
	public function commit()
	{
		return fbird_commit($this->trans);
	}

	// --------------------------------------------------------------------------

	/**
	 * Rollback a transaction
	 *
	 * @return bool
	 */
	public function rollBack()
	{
		return fbird_rollback($this->trans);
	}

	// --------------------------------------------------------------------------

	/**
	 * Prepare and execute a query
	 *
	 * @param string $sql
	 * @param array $args
	 * @return resource
	 */
	public function prepare_execute($sql, $args)
	{
		$query = $this->prepare($sql);

		// Set the statement in the class variable for easy later access
		$this->statement =& $query;

		return $query->execute($args);
	}

	// --------------------------------------------------------------------------

	/**
	 * Method to emulate PDO->quote
	 *
	 * @param string $str
	 * @return string
	 */
	public function quote($str, $param_type = NULL)
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
		$code = fbird_errcode();
		$msg = fbird_errmsg();

		return array(0, $code, $msg);
	}
	
	// --------------------------------------------------------------------------
	
	/**
	 * Method to emulate PDO->errorCode	 
	 *
	 * @return array
	 */
	public function errorCode()
	{
		return fbird_errcode();
	}

	// --------------------------------------------------------------------------

	/**
	 * Bind a prepared query with arguments for executing
	 *
	 * @return FALSE
	 */
	public function prepare_query($sql, $params)
	{
		// You can't bind query statements before execution with
		// the firebird database
		return FALSE;
	}
}
// End of firebird_driver.php