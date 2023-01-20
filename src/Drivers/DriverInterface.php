<?php declare(strict_types=1);
/**
 * Query
 *
 * SQL Query Builder / Database Abstraction Layer
 *
 * PHP version 8.1
 *
 * @package     Query
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2012 - 2022 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link        https://git.timshomepage.net/aviat/Query
 * @version     4.0.0
 */
namespace Query\Drivers;

use InvalidArgumentException;
use PDO;
use PDOStatement;

/**
 * PDO Interface to implement for database drivers
 *
 * @method beginTransaction(): bool
 * @method commit(): bool
 * @method errorCode(): string
 * @method errorInfo(): array
 * @method exec(string $statement): int
 * @method getAttribute(int $attribute)
 * @method inTransaction(): bool
 * @method lastInsertId(string $name = NULL): string
 * @method prepare(string $statement, array $driver_options = []): PDOStatement
 * @method query(string $statement): PDOStatement
 * @method quote(string $string, int $parameter_type = PDO::PARAM_STR): string
 * @method rollback(): bool
 * @method setAttribute(int $attribute, $value): bool
 */
interface DriverInterface /* extends the interface of PDO */ {

	/**
	 * Constructor/Connection method
	 */
	public function __construct(string $dsn, string $username=NULL, string $password=NULL, array $driverOptions = []);

	/**
	 * Simplifies prepared statements for database queries
	 */
	public function prepareQuery(string $sql, array $data): PDOStatement;

	/**
	 * Retrieve column information for the current database table
	 */
	public function getColumns(string $table): ?array;

	/**
	 * Retrieve list of data types for the database
	 */
	public function getTypes(): ?array;

	/**
	 * Retrieve indexes for the table
	 */
	public function getIndexes(string $table): ?array;

	/**
	 * Retrieve foreign keys for the table
	 */
	public function getFks(string $table): ?array;

	/**
	 * Return list of tables for the current database
	 */
	public function getTables(): ?array;

	/**
	 * Retrieves an array of non-user-created tables for
	 * the connection/database
	 */
	public function getSystemTables(): ?array;

	/**
	 * Return schemas for databases that list them. Returns
	 * database list if schemas are databases for the current driver.
	 */
	public function getSchemas(): ?array;

	/**
	 * Return list of dbs for the current connection, if possible
	 */
	public function getDbs(): ?array;

	/**
	 * Return list of views for the current database
	 */
	public function getViews(): ?array;

	/**
	 * Return list of sequences for the current database, if they exist
	 */
	public function getSequences(): ?array;

	/**
	 * Return list of functions for the current database
	 *
	 * @deprecated Will be removed in next version
	 */
	public function getFunctions(): ?array;

	/**
	 * Return list of stored procedures for the current database
	 *
	 * @deprecated Will be removed in next version
	 */
	public function getProcedures(): ?array;

	/**
	 * Return list of triggers for the current database
	 *
	 * @deprecated Will be removed in next version
	 */
	public function getTriggers(): ?array;

	/**
	 * Surrounds the string with the databases identifier escape characters
	 */
	public function quoteIdent(string|array $ident): string|array;

	/**
	 * Quote database table name, and set prefix
	 */
	public function quoteTable(string $table): string;

	/**
	 * Create and execute a prepared statement with the provided parameters
	 */
	public function prepareExecute(string $sql, array $params): PDOStatement;

	/**
	 * Method to simplify retrieving db results for meta-data queries
	 */
	public function driverQuery(string|array $query, bool $filteredIndex=TRUE): ?array;

	/**
	 * Returns number of rows affected by an INSERT, UPDATE, DELETE type query
	 */
	public function affectedRows(): int;

	/**
	 * Return the number of rows returned for a SELECT query
	 * @see http://us3.php.net/manual/en/pdostatement.rowcount.php#87110
	 */
	public function numRows(): ?int;

	/**
	 * Prefixes a table if it is not already prefixed
	 */
	public function prefixTable(string $table): string;

	/**
	 * Create sql for batch insert
	 */
	public function insertBatch(string $table, array $data=[]): array;

	/**
	 * Creates a batch update, and executes it.
	 * Returns the number of affected rows
	 */
	public function updateBatch(string $table, array $data, string $where): array;

	/**
	 * Empty the passed table
	 */
	public function truncate(string $table): PDOStatement;

	/**
	 * Get the SQL class for the current driver
	 */
	public function getSql(): SQLInterface;

	/**
	 * Get the Util class for the current driver
	 */
	public function getUtil(): AbstractUtil;

	/**
	 * Get the version of the database engine
	 */
	public function getVersion(): string;

	/**
	 * Get the last sql query executed
	 */
	public function getLastQuery(): string;

	/**
	 * Set the last query sql
	 */
	public function setLastQuery(string $queryString): void;

	/**
	 * Set the common table name prefix
	 */
	public function setTablePrefix(string $prefix): void;
}
