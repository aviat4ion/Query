<?php
/**
 * Query
 *
 * Free Query Builder / Database Abstraction Layer
 *
 * @package		Query
 * @author		Timothy J. Warren
 * @copyright	Copyright (c) 2012
 * @link 		https://github.com/aviat4ion/Query
 * @license		http://philsturgeon.co.uk/code/dbad-license
 */

// --------------------------------------------------------------------------

/**
 * Abstract class defining database / table creation methods
 *
 * @package Query
 * @subpackage Query
 */
abstract class DB_Util {

	/**
	 * Reference to the current connection object
	 */
	private $conn;
	
	/**
	 * Save a reference to the connection object for later use
	 *
	 * @param object &$conn
	 */
	public function __construct(&$conn)
	{
		$this->conn = $conn; 
	}
	
	// --------------------------------------------------------------------------
	
	/**
	 * Enable calling driver methods
	 *
	 * @param string $method
	 * @param array $args
	 */
	public function __call($method, $args)
	{
		if (method_exists($this->conn, $method))
		{
			return call_user_func_array(array($this->conn, $method), $args);
		}
		
		return NULL;
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
// End of db_util.php