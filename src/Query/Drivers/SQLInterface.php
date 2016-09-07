<?php
/**
 * Query
 *
 * SQL Query Builder / Database Abstraction Layer
 *
 * PHP version 5.4
 *
 * @package     Query
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2012 - 2015 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link        https://git.timshomepage.net/aviat4ion/Query
 */


// --------------------------------------------------------------------------

namespace Query\Drivers;

/**
 * parent for database manipulation subclasses
 *
 * @package Query
 * @subpackage Drivers
 */
interface SQLInterface {

	/**
	 * Get database specific sql for limit clause
	 *
	 * @param string $sql
	 * @param int $limit
	 * @param int|bool $offset
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
	 * @return string|array
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

	/**
	 * Get the list of foreign keys for the current
	 * table
	 *
	 * @param string $table
	 * @return array
	 */
	public function fk_list($table);

	/**
	 * Get the list of indexes for the current table
	 *
	 * @param string $table
	 * @return array
	 */
	public function index_list($table);

}
// End of sql_interface.php