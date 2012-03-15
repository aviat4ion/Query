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
  * ODBC SQL Class
  */
class ODBC_SQL extends DB_SQL {

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
	 * Limit clause
	 *
	 * @param string $sql
	 * @param int $limit
	 * @param int $offset
	 * @return string
	 */
	public function limit($sql, $limit, $offset=FALSE)
	{
		return $sql;
	}
	
	// --------------------------------------------------------------------------
	
	/**
	 * Random ordering keyword
	 *
	 * @return string
	 */
	public function random()
	{
		return FALSE;
	}
}
// End of odbc_sql.php