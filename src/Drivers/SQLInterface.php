<?php declare(strict_types=1);
/**
 * Query
 *
 * SQL Query Builder / Database Abstraction Layer
 *
 * PHP version 7.4
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
	 */
	public function limit(string $sql, int $limit, ?int $offset=NULL): string;

	/**
	 * Modify the query to get the query plan
	 */
	public function explain(string $sql): string;

	/**
	 * Get the sql for random ordering
	 */
	public function random(): string;

	/**
	 * Returns sql to list other databases
	 */
	public function dbList(): string;

	/**
	 * Returns sql to list tables
	 */
	public function tableList(): string;

	/**
	 * Returns sql to list system tables
	 */
	public function systemTableList(): string|array;

	/**
	 * Returns sql to list views
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
	 */
	public function typeList(): string|array;

	/**
	 * Get information about the columns in the
	 * specified table
	 */
	public function columnList(string $table): string;

	/**
	 * Get the list of foreign keys for the current
	 * table
	 */
	public function fkList(string $table): string;

	/**
	 * Get the list of indexes for the current table
	 */
	public function indexList(string $table): string;
}