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
 * ODBC-specific backup, import and creation methods
 */
class ODBC_Util extends DB_Util {

	public function __construct(&$conn)
	{
		parent::__construct($conn);
	}
	
	// --------------------------------------------------------------------------
	
	public function create_table($name, $columns, array $constraints=array(), array $indexes=array())
	{
		//ODBC can't know how to create a table
		return FALSE;
	}

	// --------------------------------------------------------------------------

	/**
	 * Remove a table from the database
	 *
	 * @param string $name
	 * @return string
	 */
	public function delete_table($name)
	{
		return "DROP TABLE {$name}";
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
// End of ODBC_util.php