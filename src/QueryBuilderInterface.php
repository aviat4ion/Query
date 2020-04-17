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
interface QueryBuilderInterface {

	// --------------------------------------------------------------------------
	// ! Select Queries
	// --------------------------------------------------------------------------

	/**
	 * Specifies rows to select in a query
	 *
	 * @param string $fields
	 * @return self
	 */
	public function select(string $fields): self;

	/**
	 * Selects the maximum value of a field from a query
	 *
	 * @param string $field
	 * @param string|bool $as
	 * @return self
	 */
	public function selectMax(string $field, $as=FALSE): self;

	/**
	 * Selects the minimum value of a field from a query
	 *
	 * @param string $field
	 * @param string|bool $as
	 * @return self
	 */
	public function selectMin(string $field, $as=FALSE): self;

	/**
	 * Selects the average value of a field from a query
	 *
	 * @param string $field
	 * @param string|bool $as
	 * @return self
	 */
	public function selectAvg(string $field, $as=FALSE): self;

	/**
	 * Selects the sum of a field from a query
	 *
	 * @param string $field
	 * @param string|bool $as
	 * @return self
	 */
	public function selectSum(string $field, $as=FALSE): self;

	/**
	 * Adds the 'distinct' keyword to a query
	 *
	 * @return self
	 */
	public function distinct(): self;

	/**
	 * Shows the query plan for the query
	 *
	 * @return self
	 */
	public function explain(): self;

	/**
	 * Specify the database table to select from
	 *
	 * @param string $tblname
	 * @return self
	 */
	public function from(string $tblname): self;

	// --------------------------------------------------------------------------
	// ! 'Like' methods
	// --------------------------------------------------------------------------

	/**
	 * Creates a Like clause in the sql statement
	 *
	 * @param string $field
	 * @param mixed $values
	 * @param string $pos
	 * @return self
	 */
	public function like(string $field, $values, string $pos='both'): self;

	/**
	 * Generates an OR Like clause
	 *
	 * @param string $field
	 * @param mixed $values
	 * @param string $pos
	 * @return self
	 */
	public function orLike(string $field, $values, string $pos='both'): self;

	/**
	 * Generates a NOT LIKE clause
	 *
	 * @param string $field
	 * @param mixed $values
	 * @param string $pos
	 * @return self
	 */
	public function notLike(string $field, $values, string $pos='both'): self;

	/**
	 * Generates a OR NOT LIKE clause
	 *
	 * @param string $field
	 * @param mixed $values
	 * @param string $pos
	 * @return self
	 */
	public function orNotLike(string $field, $values, string $pos='both'): self;

	// --------------------------------------------------------------------------
	// ! Having methods
	// --------------------------------------------------------------------------

	/**
	 * Generates a 'Having' clause
	 *
	 * @param mixed $key
	 * @param mixed $values
	 * @return self
	 */
	public function having($key, $values=[]): self;

	/**
	 * Generates a 'Having' clause prefixed with 'OR'
	 *
	 * @param mixed $key
	 * @param mixed $values
	 * @return self
	 */
	public function orHaving($key, $values=[]): self;

	// --------------------------------------------------------------------------
	// ! 'Where' methods
	// --------------------------------------------------------------------------

	/**
	 * Specify condition(s) in the where clause of a query
	 * Note: this function works with key / value, or a
	 * passed array with key / value pairs
	 *
	 * @param mixed $key
	 * @param mixed $values
	 * @param bool $escape
	 * @return self
	 */
	public function where($key, $values=[], $escape = NULL): self;

	/**
	 * Where clause prefixed with "OR"
	 *
	 * @param string $key
	 * @param mixed $values
	 * @return self
	 */
	public function orWhere($key, $values=[]): self;

	/**
	 * Where clause with 'IN' statement
	 *
	 * @param mixed $field
	 * @param mixed $values
	 * @return self
	 */
	public function whereIn($field, $values=[]): self;

	/**
	 * Where in statement prefixed with "or"
	 *
	 * @param string $field
	 * @param mixed $values
	 * @return self
	 */
	public function orWhereIn($field, $values=[]): self;

	/**
	 * WHERE NOT IN (FOO) clause
	 *
	 * @param string $field
	 * @param mixed $values
	 * @return self
	 */
	public function whereNotIn($field, $values=[]): self;

	/**
	 * OR WHERE NOT IN (FOO) clause
	 *
	 * @param string $field
	 * @param mixed $values
	 * @return self
	 */
	public function orWhereNotIn($field, $values=[]): self;

	// --------------------------------------------------------------------------
	// ! Other Query Modifier methods
	// --------------------------------------------------------------------------

	/**
	 * Sets values for inserts / updates / deletes
	 *
	 * @param mixed $key
	 * @param mixed $values
	 * @return self
	 */
	public function set($key, $values = NULL): self;

	/**
	 * Creates a join phrase in a compiled query
	 *
	 * @param string $table
	 * @param string $condition
	 * @param string $type
	 * @return self
	 */
	public function join(string $table, string $condition, string $type=''): self;

	/**
	 * Group the results by the selected field(s)
	 *
	 * @param mixed $field
	 * @return self
	 */
	public function groupBy($field): self;

	/**
	 * Order the results by the selected field(s)
	 *
	 * @param string $field
	 * @param string $type
	 * @return self
	 */
	public function orderBy(string $field, string $type=''): self;

	/**
	 * Set a limit on the current sql statement
	 *
	 * @param int $limit
	 * @param int|null $offset
	 * @return self
	 */
	public function limit(int $limit, ?int $offset=NULL): self;

	// --------------------------------------------------------------------------
	// ! Query Grouping Methods
	// --------------------------------------------------------------------------

	/**
	 * Adds a paren to the current query for query grouping
	 *
	 * @return self
	 */
	public function groupStart(): self;

	/**
	 * Adds a paren to the current query for query grouping,
	 * prefixed with 'NOT'
	 *
	 * @return self
	 */
	public function notGroupStart(): self;

	/**
	 * Adds a paren to the current query for query grouping,
	 * prefixed with 'OR'
	 *
	 * @return self
	 */
	public function orGroupStart(): self;

	/**
	 * Adds a paren to the current query for query grouping,
	 * prefixed with 'OR NOT'
	 *
	 * @return self
	 */
	public function orNotGroupStart(): self;

	/**
	 * Ends a query group
	 *
	 * @return self
	 */
	public function groupEnd(): self;

	// --------------------------------------------------------------------------
	// ! Query execution methods
	// --------------------------------------------------------------------------

	/**
	 * Select and retrieve all records from the current table, and/or
	 * execute current compiled query
	 *
	 * @param string $table
	 * @param int|null $limit
	 * @param int|null $offset
	 * @return PDOStatement
	 */
	public function get(string $table='', ?int $limit=NULL, ?int $offset=NULL): PDOStatement;

	/**
	 * Convenience method for get() with a where clause
	 *
	 * @param string $table
	 * @param array $where
	 * @param int|null $limit
	 * @param int|null $offset
	 * @return PDOStatement
	 */
	public function getWhere(string $table, $where=[], ?int $limit=NULL, ?int $offset=NULL): PDOStatement;

	/**
	 * Retrieve the number of rows in the selected table
	 *
	 * @param string $table
	 * @return int
	 */
	public function countAll(string $table): int;

	/**
	 * Retrieve the number of results for the generated query - used
	 * in place of the get() method
	 *
	 * @param string $table
	 * @param bool $reset - Whether to keep the query after counting the results
	 * @return int
	 */
	public function countAllResults(string $table='', bool $reset=TRUE): int;

	/**
	 * Creates an insert clause, and executes it
	 *
	 * @param string $table
	 * @param mixed $data
	 * @return PDOStatement
	 */
	public function insert(string $table, $data=[]): PDOStatement;

	/**
	 * Creates and executes a batch insertion query
	 *
	 * @param string $table
	 * @param array $data
	 * @return PDOStatement | null
	 */
	public function insertBatch(string $table, $data=[]): ?PDOStatement;

	/**
	 * Creates an update clause, and executes it
	 *
	 * @param string $table
	 * @param mixed $data
	 * @return PDOStatement
	 */
	public function update(string $table, $data=[]): PDOStatement;

	/**
	 * Creates a batch update, and executes it.
	 * Returns the number of affected rows
	 *
	 * @param string $table The table to update
	 * @param array $data an array of update values
	 * @param string $where The where key
	 * @return int|null
	 */
	public function updateBatch(string $table, array $data, string $where): ?int;

	/**
	 * Deletes data from a table
	 *
	 * @param string $table
	 * @param mixed $where
	 * @return PDOStatement
	 */
	public function delete(string $table, $where=''): PDOStatement;

	// --------------------------------------------------------------------------
	// ! SQL Returning Methods
	// --------------------------------------------------------------------------

	/**
	 * Returns the generated 'select' sql query
	 *
	 * @param string $table
	 * @param bool $reset
	 * @return string
	 */
	public function getCompiledSelect(string $table='', bool $reset=TRUE): string;

	/**
	 * Returns the generated 'insert' sql query
	 *
	 * @param string $table
	 * @param bool $reset
	 * @return string
	 */
	public function getCompiledInsert(string $table, bool $reset=TRUE): string;

	/**
	 * Returns the generated 'update' sql query
	 *
	 * @param string $table
	 * @param bool $reset
	 * @return string
	 */
	public function getCompiledUpdate(string $table='', bool $reset=TRUE): string;

	/**
	 * Returns the generated 'delete' sql query
	 *
	 * @param string $table
	 * @param bool $reset
	 * @return string
	 */
	public function getCompiledDelete(string $table='', bool $reset=TRUE): string;

	// --------------------------------------------------------------------------
	// ! Miscellaneous Methods
	// --------------------------------------------------------------------------

	/**
	 * Clear out the class variables, so the next query can be run
	 *
	 * @return void
	 */
	public function resetQuery(): void;
}

// End of QueryBuilderInterface.php
