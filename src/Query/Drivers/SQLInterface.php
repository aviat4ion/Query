<?php declare(strict_types=1);
/**
 * Query
 *
 * SQL Query Builder / Database Abstraction Layer
 *
 * PHP version 7
 *
 * @package     Query
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2012 - 2016 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link        https://git.timshomepage.net/aviat4ion/Query
 */

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
	public function dbList();

	/**
	 * Returns sql to list tables
	 *
	 * @return string
	 */
	public function tableList();

	/**
	 * Returns sql to list system tables
	 *
	 * @return string
	 */
	public function systemTableList();

	/**
	 * Returns sql to list views
	 *
	 * @return string
	 */
	public function viewList();

	/**
	 * Returns sql to list triggers
	 *
	 * @return string
	 */
	public function triggerList();

	/**
	 * Return sql to list functions
	 *
	 * @return NULL
	 */
	public function functionList();

	/**
	 * Return sql to list stored procedures
	 *
	 * @return string
	 */
	public function procedureList();

	/**
	 * Return sql to list sequences
	 *
	 * @return string
	 */
	public function sequenceList();

	/**
	 * Return sql to list database field types
	 *
	 * @return string|array
	 */
	public function typeList();

	/**
	 * Get information about the columns in the
	 * specified table
	 *
	 * @param string $table
	 * @return string
	 */
	public function columnList($table);

	/**
	 * Get the list of foreign keys for the current
	 * table
	 *
	 * @param string $table
	 * @return array
	 */
	public function fkList($table);

	/**
	 * Get the list of indexes for the current table
	 *
	 * @param string $table
	 * @return array
	 */
	public function indexList($table);
}