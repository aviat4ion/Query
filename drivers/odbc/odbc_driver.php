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
		parent::__construct("odbc:$dsn", $username, $password, $options);
	}

	// --------------------------------------------------------------------------

	/**
	 * Doesn't apply to ODBC
	 *
	 * @param string $name
	 * @return bool
	 */
	public function switch_db($name)
	{
		return FALSE;
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
	 * Return the number of rows returned for a SELECT query
	 *
	 * @return int
	 */
	public function num_rows()
	{
		// @TODO: Implement
	}
}
// End of odbc_driver.php