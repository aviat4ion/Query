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
 * Base Database class
 *
 * Extends PDO to simplify cross-database issues
 */
abstract class DB_PDO extends PDO {

	public $manip;
	protected $statement;

	/**
	 * PDO constructor wrapper
	 */
	public function __construct($dsn, $username=NULL, $password=NULL, $driver_options=array())
	{
		parent::__construct($dsn, $username, $password, $driver_options);
	}
	
	// -------------------------------------------------------------------------
	
	/**
	 * Simplifies prepared statements for database queries
	 *
	 * @param string $sql
	 * @param array $data
	 * @return mixed PDOStatement / FALSE
	 */
	public function prepare_query($sql, $data)
	{
		// Prepare the sql
		$query = $this->prepare($sql);
		
		if( ! (is_object($query) || is_resource($query)))
		{
			$this->get_last_error();
			return FALSE;
		}
		
		// Set the statement in the class variable for easy later access
		$this->statement =& $query;
		
		
		if( ! (is_array($data) || is_object($data)))
		{
			trigger_error("Invalid data argument");
			return FALSE;
		}
		
		// Bind the parameters
		foreach($data as $k => $value)
		{
			if(is_numeric($k))
			{
				$k++;
			}
		
			$res = $query->bindValue($k, $value);
			
			if( ! $res)
			{
				trigger_error("Parameter not successfully bound");
				return FALSE;
			}
		}
		
		return $query; 
		
	}

	// -------------------------------------------------------------------------

	/**
	 * Create and execute a prepared statement with the provided parameters
	 *
	 * @param string $sql
	 * @param array $params
	 * @return PDOStatement
	 */
	public function prepare_execute($sql, $params)
	{	
		$this->statement = $this->prepare_query($sql, $params);
		$this->statement->execute();

		return $this->statement;
	}

	// -------------------------------------------------------------------------

	/**
	 * Retreives the data from a select query
	 *
	 * @param PDOStatement $statement
	 * @return array
	 */
	public function get_query_data($statement)
	{
		$this->statement =& $statement;

		// Execute the query
		$this->statement->execute();

		// Return the data array fetched
		return $this->statement->fetchAll(PDO::FETCH_ASSOC);
	}

	// -------------------------------------------------------------------------

	/**
	 * Returns number of rows affected by an INSERT, UPDATE, DELETE type query
	 *
	 * @param PDOStatement $statement
	 * @return int
	 */
	public function affected_rows($statement='')
	{
		if ( ! empty($statement))
		{	
			$this->statement = $statement;
		}
		
		if (empty($this->statement))
		{
			return FALSE;
		}
		
		// Execute the query
		$this->statement->execute();

		// Return number of rows affected
		return $this->statement->rowCount();
	}
	
	// --------------------------------------------------------------------------
	
	/**
	 * Return the last error for the current database connection
	 *
	 * @return string
	 */
	public function get_last_error()
	{
		$info = $this->errorInfo();
		
		echo "Error: <pre>{$info[0]}:{$info[1]}\n{$info[2]}</pre>";
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
			return array_map(array($this, 'quote_ident'), $ident);
		}

		// Split each identifier by the period
		$hiers = explode('.', $ident);

		return '"'.implode('"."', $hiers).'"';
	}

	// -------------------------------------------------------------------------

	/**
	 * Deletes all the rows from a table. Does the same as the truncate
	 * method if the database does not support 'TRUNCATE';
	 *
	 * @param string $table
	 * @return mixed
	 */
	public function empty_table($table)
	{
		$sql = 'DELETE FROM '.$this->quote_ident($table);

		return $this->query($sql);
	}

	// -------------------------------------------------------------------------

	/**
	 * Abstract public functions to override in child classes
	 */
	
	/**
	 * Return list of tables for the current database
	 * 
	 * @return array
	 */
	abstract public function get_tables();

	/**
	 * Empty the passed table
	 * 
	 * @param string $table
	 * 
	 * @return void
	 */
	abstract public function truncate($table);

	/**
	 * Return the number of rows for the last SELECT query
	 * 
	 * @return int
	 */
	abstract public function num_rows();

	/**
	 * Retreives an array of non-user-created tables for 
	 * the connection/database
	 * 
	 * @return array
	 */
	abstract public function get_system_tables();
	
	/**
	 * Return an SQL file with the database table structure
	 *
	 * @return string
	 */
	abstract public function backup_structure();
	
	/**
	 * Return an SQL file with the database data as insert statements
	 *
	 * @return string
	 */
	abstract public function backup_data();
}

// -------------------------------------------------------------------------

/**
 * Abstract parent for database manipulation subclasses
 */
abstract class DB_SQL {
	
	/**
	 * Get database-specific sql to create a new table
	 * 
	 * @param string $name 
	 * @param array $columns 
	 * @param array $constraints 
	 * @param array $indexes 
	 * @return string
	 */
	abstract public function create_table($name, $columns, array $constraints=array(), array $indexes=array());

	/**
	 * Get database-specific sql to drop a table
	 * 
	 * @param string $name
	 * @return string
	 */
	abstract public function delete_table($name);

	/**
	 * Get database specific sql for limit clause
	 *
	 * @param string $sql
	 * @param int $limiit
	 * @param int $offset
	 * @return string
	 */
	abstract public function limit($sql, $limit, $offset=FALSE);
	
	/**
	 * Get the sql for random ordering
	 *
	 * @return string
	 */
	abstract public function random();
}
// End of db_pdo.php