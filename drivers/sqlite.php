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
 * SQLite specific class 
 *
 * @extends DB_PDO
 */
class SQLite extends DB_PDO {

	protected $statement;

	/**
	 * Open SQLite Database
	 * 
	 * @param string $dsn 
	 */
	public function __construct($dsn, $user=NULL, $pass=NULL)
	{
		// DSN is simply `sqlite:/path/to/db`
		parent::__construct("sqlite:{$dsn}", $user, $pass);

		$class = __CLASS__."_sql";
		$this->sql = new $class;
	}
	
	// --------------------------------------------------------------------------

	/**
	 * Empty a table
	 *
	 * @param string $table
	 */
	public function truncate($table)
	{
		// SQLite has a TRUNCATE optimization,
		// but no support for the actual command.
		$sql = 'DELETE FROM "'.$table.'"';

		$this->statement = $this->query($sql);
		
		return $this->statement;
	}
	
	// --------------------------------------------------------------------------

	/**
	 * List tables for the current database
	 * 
	 * @return mixed
	 */
	public function get_tables()
	{	
		$tables = array();
		$sql = <<<SQL
			SELECT "name", "sql" 
			FROM "sqlite_master" 
			WHERE "type"='table'
SQL;

		$res = $this->query($sql);
		$result = $res->fetchAll(PDO::FETCH_ASSOC);
		
		foreach($result as $r)
		{
			$tables[$r['name']] = $r['sql'];
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
		//SQLite only has the sqlite_master table
		// that is of any importance.
		return array('sqlite_master');
	}
	
	// --------------------------------------------------------------------------

	/**
	 * Load a database for the current connection
	 * 
	 * @param string $db
	 * @param string $name 
	 */
	public function load_database($db, $name)
	{
		$sql = 'ATTACH DATABASE "'.$db.'" AS "'.$name.'"';
		$this->query($sql);
	}
	
	// --------------------------------------------------------------------------

	/**
	 * Unload a database from the current connection
	 * 
	 * @param string $name
	 */
	public function unload_database($name)
	{
		$sql = 'DETACH DATABASE ":name"';
		
		$this->prepare_query($sql, array(
			':name' => $name,
		));

		$this->statement->execute();
	}
	
	// --------------------------------------------------------------------------

	/**
	 * Return the number of rows returned for a SELECT query
	 * 
	 * @return int
	 */
	public function num_rows()
	{
		return (isset($this->statement)) ? $this->statement->rowCount : FALSE;
	}
	
	// --------------------------------------------------------------------------
	
	/**
	 * Create an SQL backup file for the current database's structure
	 *
	 * @return string
	 */
	public function backup_structure()
	{
		// Fairly easy for SQLite...just query the master table
		$sql = 'SELECT "sql" FROM "sqlite_master"';
		$res = $this->query($sql);
		$result = $res->fetchAll(PDO::FETCH_ASSOC);

		$sql_array = array();

		foreach($result as $r)
		{
			$sql_array[] = $r['sql'];
		}

		$sql_structure = implode("\n\n", $sql_array);
		
		return $sql_structure;	
	}
	
	// --------------------------------------------------------------------------
	
	/**
	 * Create an SQL backup file for the current database's data
	 *
	 * @param array $excluded
	 * @return string
	 */
	public function backup_data($excluded=array())
	{
		// Get a list of all the objects
		$sql = 'SELECT "name" FROM "sqlite_master"';

		if( ! empty($excluded))
		{
			$sql .= ' WHERE NOT IN("'.implode('","', $excluded).'")';
		}

		$res = $this->query($sql);
		$result = $res->fetchAll(PDO::FETCH_ASSOC);
		
		unset($res);
		
		$output_sql = '';
		
		// Get the data for each object
		foreach($result as $r)
		{
			$sql = 'SELECT * FROM "'.$r['name'].'"';
			$res = $this->query($sql);
			$obj_res = $res->fetchAll(PDO::FETCH_ASSOC);
			
			unset($res);
			
			// Nab the column names by getting the keys of the first row
			$columns = array_keys($obj_res[0]);
			
			$insert_rows = array();
			
			// Create the insert statements
			foreach($obj_res as $row)
			{
				$row = array_values($row);
			
				// Quote values as needed by type
				for($i=0, $icount=count($row); $i<$icount; $i++)
				{
					$row[$i] = (is_numeric($row[$i])) ? $row[$i] : $this->quote($row[$i]);
				}
				
				$row_string = 'INSERT INTO "'.$r['name'].'" ("'.implode('","', $columns).'") VALUES ('.implode(',', $row).');';
				
				unset($row);
				
				$insert_rows[] = $row_string;
			}
			
			unset($obj_res);
			
			$output_sql .= "\n\n".implode("\n", $insert_rows);
		}
		
		return $output_sql;
	}
}
//End of sqlite.php