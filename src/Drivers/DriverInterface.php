<?php declare(strict_types=1);
/**
 * Query
 *
 * SQL Query Builder / Database Abstraction Layer
 *
 * PHP version 7.1
 *
 * @package     Query
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2012 - 2018 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link        https://git.timshomepage.net/aviat4ion/Query
 */
namespace Query\Drivers;

use InvalidArgumentException;
use PDOStatement;

/**
 * PDO Interface to implement for database drivers
 */
interface DriverInterface {

	/**
	 * Constructor/Connection method
	 *
	 * @param string $dsn
	 * @param string $username
	 * @param string $password
	 * @param array $driverOptions
	 */
	public function __construct(string $dsn, string $username=NULL, string $password=NULL, array $driverOptions = []);

	/**
	 * Simplifies prepared statements for database queries
	 *
	 * @param string $sql
	 * @param array $data
	 * @return PDOStatement|null
	 * @throws InvalidArgumentException
	 */
	public function prepareQuery(string $sql, array $data): PDOStatement;

	/**
	 * Retrieve column information for the current database table
	 *
	 * @param string $table
	 * @return array
	 */
	public function getColumns($table): ?array;

	/**
	 * Retrieve list of data types for the database
	 *
	 * @return array
	 */
	public function getTypes(): ?array;

	/**
	 * Retrieve indexes for the table
	 *
	 * @param string $table
	 * @return array
	 */
	public function getIndexes($table): ?array;

	/**
	 * Retrieve foreign keys for the table
	 *
	 * @param string $table
	 * @return array
	 */
	public function getFks($table): ?array;

	/**
	 * Return list of tables for the current database
	 *
	 * @return array
	 */
	public function getTables(): ?array;

	/**
	 * Retrieves an array of non-user-created tables for
	 * the connection/database
	 *
	 * @return array
	 */
	public function getSystemTables(): ?array;

	/**
	 * Return list of dbs for the current connection, if possible
	 *
	 * @return array
	 */
	public function getDbs(): ?array;

	/**
	 * Return list of views for the current database
	 *
	 * @return array
	 */
	public function getViews(): ?array;

	/**
	 * Return list of sequences for the current database, if they exist
	 *
	 * @return array
	 */
	public function getSequences(): ?array;

	/**
	 * Return list of functions for the current database
	 *
	 * @return array
	 */
	public function getFunctions(): ?array;

	/**
	 * Return list of stored procedures for the current database
	 *
	 * @return array
	 */
	public function getProcedures(): ?array;

	/**
	 * Return list of triggers for the current database
	 *
	 * @return array
	 */
	public function getTriggers(): ?array;

	/**
	 * Surrounds the string with the databases identifier escape characters
	 *
	 * @param string|array $ident
	 * @return string|array
	 */
	public function quoteIdent($ident);

	/**
	 * Quote database table name, and set prefix
	 *
	 * @param string|array $table
	 * @return string|array
	 */
	public function quoteTable($table);

	/**
	 * Create and execute a prepared statement with the provided parameters
	 *
	 * @param string $sql
	 * @param array $params
	 * @return PDOStatement
	 */
	public function prepareExecute(string $sql, array $params): PDOStatement;

	/**
	 * Method to simplify retrieving db results for meta-data queries
	 *
	 * @param string|array|null $query
	 * @param bool $filteredIndex
	 * @return array
	 */
	public function driverQuery($query, $filteredIndex=TRUE): ?array;

	/**
	 * Returns number of rows affected by an INSERT, UPDATE, DELETE type query
	 *
	 * @return int
	 */
	public function affectedRows(): int;

	/**
	 * Return the number of rows returned for a SELECT query
	 * @see http://us3.php.net/manual/en/pdostatement.rowcount.php#87110
	 *
	 * @return int
	 */
	public function numRows(): ?int;

	/**
	 * Prefixes a table if it is not already prefixed
	 *
	 * @param string $table
	 * @return string
	 */
	public function prefixTable(string $table): string;

	/**
	 * Create sql for batch insert
	 *
	 * @param string $table
	 * @param array $data
	 * @return array
	 */
	public function insertBatch(string $table, array $data=[]): array;

	/**
	 * Creates a batch update, and executes it.
	 * Returns the number of affected rows
	 *
	 * @param string $table
	 * @param array $data
	 * @param string $where
	 * @return array
	 */
	public function updateBatch(string $table, array $data, string $where): array;

	/**
	 * Get the SQL class for the current driver
	 *
	 * @return SQLInterface
	 */
	public function getSql(): SQLInterface;

	/**
	 * Get the Util class for the current driver
	 *
	 * @return AbstractUtil
	 */
	public function getUtil(): AbstractUtil;

	/**
	 * Set the last query sql
	 *
	 * @param string $queryString
	 * @return void
	 */
	public function setLastQuery(string $queryString): void;
}
