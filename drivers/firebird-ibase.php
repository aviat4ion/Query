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
 * PDO-firebird isn't stable, so this is a wrapper of the ibase_ public functions.
 */
class firebird extends DB_PDO {

	protected $statement, $statement_link, $trans, $count, $result, $conn;
	
	/**
	 * Open the link to the database
	 * 
	 * @param string $db
	 * @param string $user 
	 * @param string $pass
	 */
	public function __construct($dbpath, $user='sysdba', $pass='masterkey')
	{
		$this->conn = @ibase_connect($dbpath, $user, $pass, 'utf-8');

		// Throw an exception to make this match other pdo classes
		if ( ! is_resource($this->conn))
		{
			throw new PDOException(ibase_errmsg());
			die();
		}
		
		$class = __CLASS__."_sql";
		$this->sql = new $class;
	}
	
	// --------------------------------------------------------------------------

	/**
	 * Close the link to the database
	 */
	public function __destruct()
	{
		@ibase_close();
		@ibase_free_result($this->statement);
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
	 * @param  array $params
	 * @return $this
	 */
	public function query($sql)
	{
		$this->count = 0;
		
		if (isset($this->trans))
		{
			$this->statement_link = @ibase_query($this->trans, $sql);
		}
		else
		{
			$this->statement_link = @ibase_query($this->conn, $sql);
		}

		// Throw the error as a exception
		if ($this->statement_link === FALSE)
		{
			throw new PDOException(ibase_errmsg());
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
		$this->statement_link = @ibase_prepare($this->conn, $query);

		// Throw the error as an exception
		if ($this->statement_link === FALSE)
		{
			throw new PDOException(ibase_errmsg());
		}

		return new FireBird_Result($this->statement_link);
	}
	
	// --------------------------------------------------------------------------

	/**
	 * List tables for the current database
	 * 
	 * @return array
	 */
	public function get_tables()
	{	
		$sql = <<<SQL
			SELECT "RDB\$RELATION_NAME" FROM "RDB\$RELATIONS" 
			WHERE "RDB\$RELATION_NAME" NOT LIKE 'RDB$%'
			AND "RDB\$RELATION_NAME" NOT LIKE 'MON$%'
SQL;

		$this->statement = $this->query($sql);
		
		$tables = array();
		
		while($row = $this->statement->fetch(PDO::FETCH_ASSOC))
		{
			$tables[] = $row['RDB$RELATION_NAME'];
		}
		
		return $tables;
	}
	
	// --------------------------------------------------------------------------

	/**
	 * List system tables for the current database
	 * 
	 * @return array
	 */
	public function get_system_tables()
	{
		$sql = <<<SQL
			SELECT "RDB\$RELATION_NAME" FROM "RDB\$RELATIONS"
			WHERE "RDB\$RELATION_NAME" LIKE 'RDB$%'
			OR "RDB\$RELATION_NAME" LIKE 'MON$%';
SQL;

		$this->statement = $this->query($sql);

		$tables = array();

		while($row = $this->statement->fetch(PDO::FETCH_ASSOC))
		{
			$tables[] = $row['RDB$RELATION_NAME'];
		}
		
		return $tables;
	}
	
	// --------------------------------------------------------------------------

	/**
	 * Return the number of rows returned for a SELECT query
	 * 
	 * @return int
	 */
	public function num_rows()
	{
		// @todo: Redo this similar to the codeigniter driver
		if(isset($this->result))
		{
			return count($this->result);
		}

		//Fetch all the rows for the result
		$this->result = $this->statement->fetchAll();

		return count($this->result);
	}
	
	// --------------------------------------------------------------------------
	
	/**
	 * Start a database transaction
	 * 
	 * @return bool
	 */
	public function beginTransaction()
	{
		if(($this->trans = ibase_trans($this->conn)) !== NULL)
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
		return ibase_commit($this->trans);
	}
	
	// --------------------------------------------------------------------------
	
	/**
	 * Rollback a transaction
	 * 
	 * @return bool
	 */
	public function rollBack()
	{
		return ibase_rollback($this->trans);
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
	public function quote($str, $param_type=NULL)
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
		$code = ibase_errcode();
		$msg = ibase_errmsg();

		return array(0, $code, $msg);
	}
	
	// --------------------------------------------------------------------------
	
	/**
	 * Bind a prepared query with arguments for executing
	 *
	 * @param string $sql
	 * @param mixed $args
	 * @return FALSE
	 */
	public function prepare_query($sql, $args)
	{
		// You can't bind query statements before execution with
		// the firebird database
		return FALSE;
	}
	
	// --------------------------------------------------------------------------
	
	/**
	 * Create an SQL backup file for the current database's structure
	 *
	 * @return string
	 */
	public function backup_structure()
	{
		// @todo Implement Backup function
		return '';	
	}
	
	// --------------------------------------------------------------------------
	
	/**
	 * Create an SQL backup file for the current database's data
	 *
	 * @param array $exclude
	 * @param bool $system_tables
	 * @return string
	 */
	public function backup_data($exclude=array(), $system_tables=FALSE)
	{
		// Determine which tables to use
		if($system_tables == TRUE)
		{
			$tables = array_merge($this->get_system_tables(), $this->get_tables());
		}
		else
		{
			$tables = $this->get_tables();
		}
		
		// Filter out the tables you don't want
		if( ! empty($exclude))
		{
			$tables = array_diff($tables, $exclude);
		}
		
		$output_sql = '';
		
		// Get the data for each object
		foreach($tables as $t)
		{
			$sql = 'SELECT * FROM "'.trim($t).'"';
			$res = $this->query($sql);
			$obj_res = $this->fetchAll(PDO::FETCH_ASSOC);
			
			unset($res);
			
			// Nab the column names by getting the keys of the first row
			$columns = @array_keys($obj_res[0]);
			
			$insert_rows = array();
			
			// Create the insert statements
			foreach($obj_res as $row)
			{
				$row = array_values($row);
			
				// Quote values as needed by type
				if(stripos($t, 'RDB$') === FALSE)
				{
					$row = array_map(array(&$this, 'quote'), $row);
					$row = array_map('trim', $row);
				}
				
				$row_string = 'INSERT INTO "'.trim($t).'" ("'.implode('","', $columns).'") VALUES ('.implode(',', $row).');';
				
				unset($row);
				
				$insert_rows[] = $row_string;
			}
			
			unset($obj_res);
			
			$output_sql .= "\n\nSET TRANSACTION;\n".implode("\n", $insert_rows)."\nCOMMIT;";
		}
		
		return $output_sql;
	}
}

// --------------------------------------------------------------------------

/**
 * Firebird result class to emulate PDOStatement Class
 */
class Firebird_Result {

	private $statement;
	
	/**
	 * Create the object by passing the resource for
	 * the query
	 *
	 * @param resource $link
	 */
	public function __construct($link)
	{
		$this->statement = $link;
	}
	
	// --------------------------------------------------------------------------

	/**
	 * Emulate PDO fetch public function
	 * 
	 * @param  int $fetch_style
	 * @return mixed
	 */
	public function fetch($fetch_style=PDO::FETCH_ASSOC, $statement=NULL)
	{
		if ( ! is_null($statement))
		{
			$this->statement = $statement;
		}
	
		switch($fetch_style)
		{
			case PDO::FETCH_OBJ:
				return ibase_fetch_object($this->statement, IBASE_FETCH_BLOBS);
			break;

			case PDO::FETCH_NUM:
				return ibase_fetch_row($this->statement, IBASE_FETCH_BLOBS);
			break;

			default:
				return ibase_fetch_assoc($this->statement, IBASE_FETCH_BLOBS);
			break;
		}
	}
	
	// --------------------------------------------------------------------------

	/**
	 * Emulate PDO fetchAll public function
	 * 
	 * @param  int  $fetch_style
	 * @return mixed
	 */
	public function fetchAll($fetch_style=PDO::FETCH_ASSOC, $statement=NULL)
	{
		$all = array();

		while($row = $this->fetch($fetch_style, $statement))
		{
			$all[] = $row;
		}
		
		$this->result = $all;

		return $all;
	}
	
	// --------------------------------------------------------------------------
	
	/**
	 * Run a prepared statement query
	 * 
	 * @param  array $args
	 * @return bool
	 */
	public function execute($args)
	{
		//Add the prepared statement as the first parameter
		array_unshift($args, $this->statement);
		
		// Let php do all the hard stuff in converting 
		// the array of arguments into a list of arguments
		return new Firebird_Result(call_user_func_array('ibase_execute', $args));
	}
	
	// --------------------------------------------------------------------------

	/**
	 * Return the number of rows affected by the previous query
	 * 
	 * @return int
	 */
	public function rowCount($statement="")
	{
		return ibase_affected_rows();
	}
	
	// --------------------------------------------------------------------------

	/**
	 * Method to emulate PDO->errorInfo / PDOStatement->errorInfo
	 *
	 * @return array
	 */
	public function errorInfo()
	{
		$code = ibase_errcode();
		$msg = ibase_errmsg();

		return array(0, $code, $msg);
	}
}
// End of firebird-ibase.php