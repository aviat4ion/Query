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
  * ODBC Database Driver
  *
  * For general database access for databases not specified by the main drivers
  *
  * @extends DB_PDO
  */
class ODBC extends DB_PDO {

	public function __construct($dsn, $username=null, $password=null, $options=array())
	{
		parent::__construct("odbc:$dsn", $username, $password, $options);

		$class = __CLASS__.'_sql';
		$this->sql = new $class;
	}
	
	// --------------------------------------------------------------------------

	/**
	 * List tables for the current database
	 * 
	 * @return mixed
	 */
	public function get_tables()
	{	
		//Not possible reliably with this driver
		return FALSE;
	}
	
	// --------------------------------------------------------------------------

	/**
	 * List system tables for the current database/connection
	 * 
	 * @return  array
	 */
	public function get_system_tables()
	{
		//No way of determining for ODBC
		return array();
	}
	
	// --------------------------------------------------------------------------

	/**
	 * Empty the current database
	 * 
	 * @return void
	 */
	public function truncate($table)
	{
		$sql = "DELETE FROM {$table}";
		$this->query($sql);
	}
	
	// --------------------------------------------------------------------------

	/**
	 * Return the number of rows returned for a SELECT query
	 * 
	 * @return int
	 */
	public function num_rows()
	{
		// TODO: Implement
	}
	
	// --------------------------------------------------------------------------
	
	/**
	 * Create an SQL backup file for the current database's structure
	 *
	 * @return string
	 */
	public function backup_structure()
	{
		// Not applicable to ODBC
		return '';	
	}
	
	// --------------------------------------------------------------------------
	
	/**
	 * Create an SQL backup file for the current database's data
	 *
	 * @return string
	 */
	public function backup_data()
	{
		// Not applicable to ODBC
		return '';
	}
}
// End of odbc.php