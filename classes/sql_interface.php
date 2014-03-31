<?php
/**
 * Query
 *
 * Free Query Builder / Database Abstraction Layer
 *
 * @author		Timothy J. Warren
 * @copyright	Copyright (c) 2012 - 2014
 * @link 		https://github.com/aviat4ion/Query
 * @license		http://philsturgeon.co.uk/code/dbad-license
 * @package		Query
 */

// --------------------------------------------------------------------------

/**
 * parent for database manipulation subclasses
 *
 * @package Query
 * @subpackage Drivers
 */
interface SQL_Interface {

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
	 * Modify the query to get the query plan
	 *
	 * @param string $sql
	 * @return string
	 */
	public function explain($sql);

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
	 * @return NULL
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
	 * @param string $table
	 * @return string
	 */
	public function column_list($table);

}
// End of sql_interface.php