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
 * Firebird Database class
 *
 * PDO-firebird isn't stable, so this is a wrapper of the fbird_ public functions.
 *
 * @package Query
 * @subpackage Drivers
 */
class PDO_Firebird extends DB_PDO {

	/**
	 * Reference to the last query executed
	 *
	 * @var object
	 */
	protected $statement;

	/**
	 * Open the link to the database
	 *
	 * @param string $dsn
	 * @param string $user
	 * @param string $pass
	 * @param array $options
	 */
	public function __construct($dsn, $user='SYSDBA', $pass='masterkey', $options = array())
	{
		if (strpos($dsn, 'firebird') === FALSE) $dsn = 'firebird:'.$dsn;
		
		parent::__construct($dsn, $username, $password, $options);
	}

	// --------------------------------------------------------------------------

	/**
	 * Empty a database table
	 *
	 * @param string $table
	 */
	public function truncate($table)
	{
		// Firebird lacks a truncate command
		$sql = 'DELETE FROM "'.$table.'"';
		$this->statement = $this->query($sql);
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
	 * @return string
	 */
	public function insert_batch($table, $data=array())
	{
		// This is not very applicable to the firebird database
		return NULL;
	}
}
// End of firebird_driver.php