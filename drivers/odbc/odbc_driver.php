<?php
/**
 * Query
 *
 * Free Query Builder / Database Abstraction Layer
 *
 * @package		Query
 * @author		Timothy J. Warren
 * @copyright	Copyright (c) 2012 - 2013
 * @link 		https://github.com/aviat4ion/Query
 * @license		http://philsturgeon.co.uk/code/dbad-license
 */

// --------------------------------------------------------------------------

 /**
  * ODBC Database Driver
  *
  * For general database access for databases not specified by the main drivers
  *
  * @package Query
  * @subpackage Drivers
  */
class ODBC extends DB_PDO {

	/**
	 * Don't define the escape char - or define it in sub-drivers in a refactor
	 */
	protected $escape_char = '';

	/**
	 * Use ODBC to connect to a database
	 *
	 * @param string $dsn
	 * @param string $username
	 * @param string $password
	 * @param array $options
	 */
	public function __construct($dsn, $username=null, $password=null, $options=array())
	{
		parent::__construct("odbc:{$dsn}", $username, $password, $options);
	}

	// --------------------------------------------------------------------------

	/**
	 * Empty the current database
	 *
	 * @param string $table
	 * @return void
	 */
	public function truncate($table)
	{
		$sql = "DELETE FROM {$table}";
		$this->query($sql);
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
// End of odbc_driver.php