<?php declare(strict_types=1);
/**
 * Query
 *
 * SQL Query Builder / Database Abstraction Layer
 *
 * PHP version 8.1
 *
 * @package     Query
 * @author      Timothy J. Warren <tim@timshome.page>
 * @copyright   2012 - 2023 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link        https://git.timshomepage.net/aviat/Query
 * @version     4.0.0
 */

namespace Query;

use PDO;
use PDOStatement;

/**
 * Interface defining the Query Builder class
 *
 * @method affectedRows(): int
 * @method beginTransaction(): bool
 * @method commit(): bool
 * @method errorCode(): string
 * @method errorInfo(): array
 * @method exec(string $statement): int
 * @method getAttribute(int $attribute)
 * @method getColumns(string $table): array | null
 * @method getDbs(): array | null
 * @method getFks(string $table): array | null
 * @method getFunctions(): array | null
 * @method getIndexes(string $table): array | null
 * @method getLastQuery(): string
 * @method getProcedures(): array | null
 * @method getSchemas(): array | null
 * @method getSequences(): array | null
 * @method getSystemTables(): array | null
 * @method getTables(): array
 * @method getTriggers(): array | null
 * @method getTypes(): array | null
 * @method getUtil(): \Query\Drivers\AbstractUtil
 * @method getVersion(): string
 * @method getViews(): array | null
 * @method inTransaction(): bool
 * @method lastInsertId(string $name = NULL): string
 * @method numRows(): int | null
 * @method prepare(string $statement, array $driver_options = []): PDOStatement
 * @method prepareExecute(string $sql, array $params): PDOStatement
 * @method prepareQuery(string $sql, array $data): PDOStatement
 * @method query(string $statement): PDOStatement
 * @method quote(string $string, int $parameter_type = PDO::PARAM_STR): string
 * @method rollback(): bool
 * @method setAttribute(int $attribute, $value): bool
 * @method setTablePrefix(string $prefix): void
 * @method truncate(string $table): PDOStatement
 */
interface QueryBuilderInterface
{
	// --------------------------------------------------------------------------
	// ! Select Queries
	// --------------------------------------------------------------------------
	/**
	 * Specifies rows to select in a query
	 */
	public function select(string $fields): self;

	/**
	 * Selects the maximum value of a field from a query
	 *
	 * @param bool|string $as
	 */
	public function selectMax(string $field, $as=FALSE): self;

	/**
	 * Selects the minimum value of a field from a query
	 *
	 * @param bool|string $as
	 */
	public function selectMin(string $field, $as=FALSE): self;

	/**
	 * Selects the average value of a field from a query
	 *
	 * @param bool|string $as
	 */
	public function selectAvg(string $field, $as=FALSE): self;

	/**
	 * Selects the sum of a field from a query
	 *
	 * @param bool|string $as
	 */
	public function selectSum(string $field, $as=FALSE): self;

	/**
	 * Adds the 'distinct' keyword to a query
	 */
	public function distinct(): self;

	/**
	 * Shows the query plan for the query
	 */
	public function explain(): self;

	/**
	 * Specify the database table to select from
	 *
	 * Alias of `from` method to better match CodeIgniter 4
	 */
	public function table(string $tableName): self;

	/**
	 * Specify the database table to select from
	 */
	public function from(string $tableName): self;

	// --------------------------------------------------------------------------
	// ! 'Like' methods
	// --------------------------------------------------------------------------
	/**
	 * Creates a Like clause in the sql statement
	 */
	public function like(string $field, mixed $values, LikeType|string $pos=LikeType::BOTH): self;

	/**
	 * Generates an OR Like clause
	 */
	public function orLike(string $field, mixed $values, LikeType|string $pos=LikeType::BOTH): self;

	/**
	 * Generates a NOT LIKE clause
	 */
	public function notLike(string $field, mixed $values, LikeType|string $pos=LikeType::BOTH): self;

	/**
	 * Generates a OR NOT LIKE clause
	 */
	public function orNotLike(string $field, mixed $values, LikeType|string $pos=LikeType::BOTH): self;

	// --------------------------------------------------------------------------
	// ! Having methods
	// --------------------------------------------------------------------------
	/**
	 * Generates a 'Having' clause
	 */
	public function having(mixed $key, mixed $values=[]): self;

	/**
	 * Generates a 'Having' clause prefixed with 'OR'
	 */
	public function orHaving(mixed $key, mixed $values=[]): self;

	// --------------------------------------------------------------------------
	// ! 'Where' methods
	// --------------------------------------------------------------------------
	/**
	 * Specify condition(s) in the where clause of a query
	 * Note: this function works with key / value, or a
	 * passed array with key / value pairs
	 */
	public function where(mixed $key, mixed $values=[]): self;

	/**
	 * Where clause prefixed with "OR"
	 *
	 * @param string $key
	 */
	public function orWhere(mixed $key, mixed $values=[]): self;

	/**
	 * Where clause with 'IN' statement
	 */
	public function whereIn(string $field, mixed $values=[]): self;

	/**
	 * Where in statement prefixed with "or"
	 */
	public function orWhereIn(string $field, mixed $values=[]): self;

	/**
	 * WHERE NOT IN (FOO) clause
	 */
	public function whereNotIn(string $field, mixed $values=[]): self;

	/**
	 * OR WHERE NOT IN (FOO) clause
	 */
	public function orWhereNotIn(string $field, mixed $values=[]): self;

	// --------------------------------------------------------------------------
	// ! Other Query Modifier methods
	// --------------------------------------------------------------------------
	/**
	 * Sets values for inserts / updates / deletes
	 *
	 * @param mixed $values
	 */
	public function set(mixed $key, mixed $values = NULL): self;

	/**
	 * Creates a join phrase in a compiled query
	 */
	public function join(string $table, string $condition, JoinType|string $type=JoinType::INNER): self;

	/**
	 * Group the results by the selected field(s)
	 */
	public function groupBy(mixed $field): self;

	/**
	 * Order the results by the selected field(s)
	 */
	public function orderBy(string $field, string $type=''): self;

	/**
	 * Set a limit on the current sql statement
	 */
	public function limit(int $limit, ?int $offset=NULL): self;

	// --------------------------------------------------------------------------
	// ! Query Grouping Methods
	// --------------------------------------------------------------------------
	/**
	 * Adds a paren to the current query for query grouping
	 */
	public function groupStart(): self;

	/**
	 * Adds a paren to the current query for query grouping,
	 * prefixed with 'NOT'
	 */
	public function notGroupStart(): self;

	/**
	 * Adds a paren to the current query for query grouping,
	 * prefixed with 'OR'
	 */
	public function orGroupStart(): self;

	/**
	 * Adds a paren to the current query for query grouping,
	 * prefixed with 'OR NOT'
	 */
	public function orNotGroupStart(): self;

	/**
	 * Ends a query group
	 */
	public function groupEnd(): self;

	// --------------------------------------------------------------------------
	// ! Query execution methods
	// --------------------------------------------------------------------------
	/**
	 * Select and retrieve all records from the current table, and/or
	 * execute current compiled query
	 */
	public function get(string $table='', ?int $limit=NULL, ?int $offset=NULL): PDOStatement;

	/**
	 * Convenience method for get() with a where clause
	 */
	public function getWhere(string $table, array $where=[], ?int $limit=NULL, ?int $offset=NULL): PDOStatement;

	/**
	 * Retrieve the number of rows in the selected table
	 */
	public function countAll(string $table): int;

	/**
	 * Retrieve the number of results for the generated query - used
	 * in place of the get() method
	 *
	 * @param bool $reset - Whether to keep the query after counting the results
	 */
	public function countAllResults(string $table='', bool $reset=TRUE): int;

	/**
	 * Creates an insert clause, and executes it
	 */
	public function insert(string $table, mixed $data=[]): PDOStatement;

	/**
	 * Creates and executes a batch insertion query
	 *
	 * @param array $data
	 */
	public function insertBatch(string $table, mixed $data=[]): ?PDOStatement;

	/**
	 * Creates an update clause, and executes it
	 */
	public function update(string $table, mixed $data=[]): PDOStatement;

	/**
	 * Creates a batch update, and executes it.
	 * Returns the number of affected rows
	 *
	 * @param string $table The table to update
	 * @param array $data an array of update values
	 * @param string $where The where key
	 */
	public function updateBatch(string $table, array $data, string $where): ?int;

	/**
	 * Deletes data from a table
	 */
	public function delete(string $table, mixed $where=''): PDOStatement;

	// --------------------------------------------------------------------------
	// ! SQL Returning Methods
	// --------------------------------------------------------------------------
	/**
	 * Returns the generated 'select' sql query
	 */
	public function getCompiledSelect(string $table='', bool $reset=TRUE): string;

	/**
	 * Returns the generated 'insert' sql query
	 */
	public function getCompiledInsert(string $table, bool $reset=TRUE): string;

	/**
	 * Returns the generated 'update' sql query
	 */
	public function getCompiledUpdate(string $table='', bool $reset=TRUE): string;

	/**
	 * Returns the generated 'delete' sql query
	 */
	public function getCompiledDelete(string $table='', bool $reset=TRUE): string;

	// --------------------------------------------------------------------------
	// ! Miscellaneous Methods
	// --------------------------------------------------------------------------
	/**
	 * Clear out the class variables, so the next query can be run
	 */
	public function resetQuery(): void;
}

// End of QueryBuilderInterface.php
