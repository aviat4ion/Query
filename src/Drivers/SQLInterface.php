<?php declare(strict_types=1);
/**
 * Query
 *
 * SQL Query Builder / Database Abstraction Layer
 *
 * PHP version 7.2
 *
 * @package     Query
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2012 - 2020 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link        https://git.timshomepage.net/aviat/Query
 * @version     3.0.0
 */
namespace Query\Drivers;

/**
 * Interface for database-specific syntax subclasses
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
	public function limit(string $sql, int $limit, $offset=FALSE): string;

	/**
	 * Modify the query to get the query plan
	 *
	 * @param string $sql
	 * @return string
	 */
	public function explain(string $sql): string;

	/**
	 * Get the sql for random ordering
	 *
	 * @return string
	 */
	public function random(): string;

	/**
	 * Returns sql to list other databases
	 *
	 * @return string
	 */
	public function dbList(): string;

	/**
	 * Returns sql to list tables
	 *
	 * @return string
	 */
	public function tableList(): string;

	/**
	 * Returns sql to list system tables
	 *
	 * @return string|array
	 */
	public function systemTableList();

	/**
	 * Returns sql to list views
	 *
	 * @return string
	 */
	public function viewList(): string;

	/**
	 * Returns sql to list triggers
	 *
	 * @return string
	 */
	public function triggerList(): ?string;

	/**
	 * Return sql to list functions
	 *
	 * @return string
	 */
	public function functionList(): ?string;

	/**
	 * Return sql to list stored procedures
	 *
	 * @return string
	 */
	public function procedureList(): ?string;

	/**
	 * Return sql to list sequences
	 *
	 * @return string
	 */
	public function sequenceList(): ?string;

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
	public function columnList(string $table): string;

	/**
	 * Get the list of foreign keys for the current
	 * table
	 *
	 * @param string $table
	 * @return string
	 */
	public function fkList(string $table): string;

	/**
	 * Get the list of indexes for the current table
	 *
	 * @param string $table
	 * @return string
	 */
	public function indexList(string $table): string;
}