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
 * ODBC SQL Class
 *
 * @package Query
 * @subpackage Drivers
 */
class ODBC_SQL implements iDB_SQL {

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
		return NULL;
	}

	// --------------------------------------------------------------------------

	/**
	 * Returns sql to list other databases
	 *
	 * @return NULL
	 */
	public function db_list()
	{
		return NULL;
	}

	// --------------------------------------------------------------------------

	/**
	 * Returns sql to list tables
	 *
	 * @return NULL
	 */
	public function table_list()
	{
		return NULL;
	}

	// --------------------------------------------------------------------------

	/**
	 * Returns sql to list system tables
	 *
	 * @return NULL
	 */
	public function system_table_list()
	{
		return NULL;
	}

	// --------------------------------------------------------------------------

	/**
	 * Returns sql to list views
	 *
	 * @return NULL
	 */
	public function view_list()
	{
		return NULL;
	}

	// --------------------------------------------------------------------------

	/**
	 * Returns sql to list triggers
	 *
	 * @return NULL
	 */
	public function trigger_list()
	{
		return NULL;
	}

	// --------------------------------------------------------------------------

	/**
	 * Return sql to list functions
	 *
	 * @return NULL
	 */
	public function function_list()
	{
		return NULL;
	}

	// --------------------------------------------------------------------------

	/**
	 * Return sql to list stored procedures
	 *
	 * @return NULL
	 */
	public function procedure_list()
	{
		return NULL;
	}

	// --------------------------------------------------------------------------

	/**
	 * Return sql to list sequences
	 *
	 * @return NULL
	 */
	public function sequence_list()
	{
		return NULL;
	}
	
	// --------------------------------------------------------------------------
	
	/**
	 * SQL to show list of field types
	 *
	 * @return NULL
	 */
	public function type_list()
	{
		return NULL;
	}
	
	// --------------------------------------------------------------------------
	
	/**
	 * SQL to show infromation about columns in a table
	 *
	 * @param string $table
	 * @return NULL
	 */
	public function column_list($table)
	{
		return NULL;
	}
	
}
// End of odbc_sql.php
