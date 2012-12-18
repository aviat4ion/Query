<?php
/**
 * Query
 *
 * Free Query Builder / Database Abstraction Layer
 *
 * @author		Timothy J. Warren
 * @copyright	Copyright (c) 2012
 * @link 		https://github.com/aviat4ion/Query
 * @license		http://philsturgeon.co.uk/code/dbad-license
 * @package		Query
 */

// --------------------------------------------------------------------------

/**
 * parent for database manipulation subclasses
 *
 * @package Query
 * @subpackage Query
 */
interface iDB_SQL {

	/**
	 * Get database specific sql for limit clause
	 *
	 * @abstract
	 * @param string $sql
	 * @param int $limit
	 * @param int $offset
	 * @return string
	 */
	public function limit($sql, $limit, $offset=FALSE);

	/**
	 * Get the sql for random ordering
	 *
	 * @abstract
	 * @return string
	 */
	public function random();
	
	/**
	 * Returns sql to list other databases
	 *
	 * @return string
	 */
	public function db_list();

	/**
	 * Returns sql to list tables
	 *
	 * @return string
	 */
	public function table_list();

	/**
	 * Returns sql to list system tables
	 *
	 * @return string
	 */
	public function system_table_list();

	/**
	 * Returns sql to list views
	 *
	 * @return string
	 */
	public function view_list();

	/**
	 * Returns sql to list triggers
	 *
	 * @return string
	 */
	public function trigger_list();

	/**
	 * Return sql to list functions
	 *
	 * @return FALSE
	 */
	public function function_list();

	/**
	 * Return sql to list stored procedures
	 *
	 * @return string
	 */
	public function procedure_list();

	/**
	 * Return sql to list sequences
	 *
	 * @return string
	 */
	public function sequence_list();
	
	/**
	 * Return sql to list database field types
	 *
	 * @return mixed
	 */
	public function type_list();
	
	/**
	 * Get information about the columns in the 
	 * specified table
	 *
	 * @param string
	 * @return string
	 */
	public function column_list($table);
}
// End of db_sql.php