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
 * Abstract class defining database / table creation methods
 */
abstract class DB_Util {

	private $conn;
	
	/**
	 * Save a reference to the connection object for later use
	 */
	public function __construct(&$conn)
	{
		$this->conn =& $conn; 
	}
	
	// --------------------------------------------------------------------------
	// ! Abstract Methods
	// --------------------------------------------------------------------------

	/**
	 * Get database-specific sql to create a new table
	 *
	 * @abstract
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
	 * @abstract
	 * @param string $name
	 * @return string
	 */
	abstract public function delete_table($name);
	
	/**
	 * Return an SQL file with the database table structure
	 *
	 * @abstract
	 * @return string
	 */
	abstract public function backup_structure();

	/**
	 * Return an SQL file with the database data as insert statements
	 *
	 * @abstract
	 * @return string
	 */
	abstract public function backup_data();

}