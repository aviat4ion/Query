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

		// --------------------------------------------------------------------------

	/**
	 * Returns sql to list other databases
	 *
	 * @return FALSE
	 */
	public function db_list()
	{
		return FALSE;
	}

	// --------------------------------------------------------------------------

	/**
	 * Returns sql to list tables
	 *
	 * @return FALSE
	 */
	public function table_list()
	{
		return FALSE;
	}

	// --------------------------------------------------------------------------

	/**
	 * Returns sql to list system tables
	 *
	 * @return FALSE
	 */
	public function system_table_list()
	{
		return FALSE;
	}

	// --------------------------------------------------------------------------

	/**
	 * Returns sql to list views
	 *
	 * @return FALSE
	 */
	public function view_list()
	{
		return FALSE;
	}

	// --------------------------------------------------------------------------

	/**
	 * Returns sql to list triggers
	 *
	 * @return FALSE
	 */
	public function trigger_list()
	{
		return FALSE;
	}

	// --------------------------------------------------------------------------

	/**
	 * Return sql to list functions
	 *
	 * @return FALSE
	 */
	public function function_list()
	{
		return FALSE;
	}

	// --------------------------------------------------------------------------

	/**
	 * Return sql to list stored procedures
	 *
	 * @return FALSE
	 */
	public function procedure_list()
	{
		return FALSE;
	}

	// --------------------------------------------------------------------------

	/**
	 * Return sql to list sequences
	 *
	 * @return FALSE
	 */
	public function sequence_list()
	{
		return FALSE;
	}
}
// End of odbc_sql.php