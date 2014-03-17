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
 * SQLite specific class
 *
 * @package Query
 * @subpackage Drivers
 */
class SQLite extends DB_PDO {

	/**
	 * Reference to the last executed sql query
	 *
	 * @var PDOStatement
	 */
	protected $statement;

	/**
	 * Open SQLite Database
	 *
	 * @param string $dsn
	 * @param string $user
	 * @param string $pass
	 */
	public function __construct($dsn, $user=NULL, $pass=NULL)
	{	
		// DSN is simply `sqlite:/path/to/db`
		parent::__construct("sqlite:{$dsn}", $user, $pass);
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
		$sql = $this->sql->table_list();

		$res = $this->query($sql);
		return db_filter($res->fetchAll(PDO::FETCH_ASSOC), 'name');
	}

	// --------------------------------------------------------------------------

	/**
	 * List system tables for the current database
	 *
	 * @return string[]
	 */
	public function get_system_tables()
	{
		//SQLite only has the sqlite_master table
		// that is of any importance.
		return array('sqlite_master');
	}
	
	// --------------------------------------------------------------------------
	
	/** 
	 * Create sql for batch insert
	 *
	 * @param string $table
	 * @param array $data
	 * @return string
	 */
	public function insert_batch($table, $data=array())
	{
		// This is not very applicable to the firebird database
		return NULL;
	}
}
//End of sqlite_driver.php