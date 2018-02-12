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
namespace Query;

use PDOStatement;

/**
 * Interface defining the Query Builder class
 */
interface QueryBuilderInterface {

	// --------------------------------------------------------------------------
	// ! Select Queries
	// --------------------------------------------------------------------------

	/**
	 * Specifies rows to select in a query
	 *
	 * @param string $fields
	 * @return QueryBuilderInterface
	 */
	public function select(string $fields): QueryBuilderInterface;

	/**
	 * Selects the maximum value of a field from a query
	 *
	 * @param string $field
	 * @param string|bool $as
	 * @return QueryBuilderInterface
	 */
	public function selectMax(string $field, $as=FALSE): QueryBuilderInterface;

	/**
	 * Selects the minimum value of a field from a query
	 *
	 * @param string $field
	 * @param string|bool $as
	 * @return QueryBuilderInterface
	 */
	public function selectMin(string $field, $as=FALSE): QueryBuilderInterface;

	/**
	 * Selects the average value of a field from a query
	 *
	 * @param string $field
	 * @param string|bool $as
	 * @return QueryBuilderInterface
	 */
	public function selectAvg(string $field, $as=FALSE): QueryBuilderInterface;

	/**
	 * Selects the sum of a field from a query
	 *
	 * @param string $field
	 * @param string|bool $as
	 * @return QueryBuilderInterface
	 */
	public function selectSum(string $field, $as=FALSE): QueryBuilderInterface;

	/**
	 * Adds the 'distinct' keyword to a query
	 *
	 * @return QueryBuilderInterface
	 */
	public function distinct(): QueryBuilderInterface;

	/**
	 * Shows the query plan for the query
	 *
	 * @return QueryBuilderInterface
	 */
	public function explain(): QueryBuilderInterface;

	/**
	 * Specify the database table to select from
	 *
	 * @param string $tblname
	 * @return QueryBuilderInterface
	 */
	public function from(string $tblname): QueryBuilderInterface;

	// --------------------------------------------------------------------------
	// ! 'Like' methods
	// --------------------------------------------------------------------------

	/**
	 * Creates a Like clause in the sql statement
	 *
	 * @param string $field
	 * @param mixed $values
	 * @param string $pos
	 * @return QueryBuilderInterface
	 */
	public function like(string $field, $values, string $pos='both'): QueryBuilderInterface;

	/**
	 * Generates an OR Like clause
	 *
	 * @param string $field
	 * @param mixed $values
	 * @param string $pos
	 * @return QueryBuilderInterface
	 */
	public function orLike(string $field, $values, string $pos='both'): QueryBuilderInterface;

	/**
	 * Generates a NOT LIKE clause
	 *
	 * @param string $field
	 * @param mixed $values
	 * @param string $pos
	 * @return QueryBuilderInterface
	 */
	public function notLike(string $field, $values, string $pos='both'): QueryBuilderInterface;

	/**
	 * Generates a OR NOT LIKE clause
	 *
	 * @param string $field
	 * @param mixed $values
	 * @param string $pos
	 * @return QueryBuilderInterface
	 */
	public function orNotLike(string $field, $values, string $pos='both'): QueryBuilderInterface;

	// --------------------------------------------------------------------------
	// ! Having methods
	// --------------------------------------------------------------------------

	/**
	 * Generates a 'Having' clause
	 *
	 * @param mixed $key
	 * @param mixed $values
	 * @return QueryBuilderInterface
	 */
	public function having($key, $values=[]): QueryBuilderInterface;

	/**
	 * Generates a 'Having' clause prefixed with 'OR'
	 *
	 * @param mixed $key
	 * @param mixed $values
	 * @return QueryBuilderInterface
	 */
	public function orHaving($key, $values=[]): QueryBuilderInterface;

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
	 * @return QueryBuilderInterface
	 */
	public function where($key, $values=[], $escape = NULL): QueryBuilderInterface;

	/**
	 * Where clause prefixed with "OR"
	 *
	 * @param string $key
	 * @param mixed $values
	 * @return QueryBuilderInterface
	 */
	public function orWhere($key, $values=[]): QueryBuilderInterface;

	/**
	 * Where clause with 'IN' statement
	 *
	 * @param mixed $field
	 * @param mixed $values
	 * @return QueryBuilderInterface
	 */
	public function whereIn($field, $values=[]): QueryBuilderInterface;

	/**
	 * Where in statement prefixed with "or"
	 *
	 * @param string $field
	 * @param mixed $values
	 * @return QueryBuilderInterface
	 */
	public function orWhereIn($field, $values=[]): QueryBuilderInterface;

	/**
	 * WHERE NOT IN (FOO) clause
	 *
	 * @param string $field
	 * @param mixed $values
	 * @return QueryBuilderInterface
	 */
	public function whereNotIn($field, $values=[]): QueryBuilderInterface;

	/**
	 * OR WHERE NOT IN (FOO) clause
	 *
	 * @param string $field
	 * @param mixed $values
	 * @return QueryBuilderInterface
	 */
	public function orWhereNotIn($field, $values=[]): QueryBuilderInterface;

	// --------------------------------------------------------------------------
	// ! Other Query Modifier methods
	// --------------------------------------------------------------------------

	/**
	 * Sets values for inserts / updates / deletes
	 *
	 * @param mixed $key
	 * @param mixed $values
	 * @return QueryBuilderInterface
	 */
	public function set($key, $values = NULL): QueryBuilderInterface;

	/**
	 * Creates a join phrase in a compiled query
	 *
	 * @param string $table
	 * @param string $condition
	 * @param string $type
	 * @return QueryBuilderInterface
	 */
	public function join(string $table, string $condition, string $type=''): QueryBuilderInterface;

	/**
	 * Group the results by the selected field(s)
	 *
	 * @param mixed $field
	 * @return QueryBuilderInterface
	 */
	public function groupBy($field): QueryBuilderInterface;

	/**
	 * Order the results by the selected field(s)
	 *
	 * @param string $field
	 * @param string $type
	 * @return QueryBuilderInterface
	 */
	public function orderBy(string $field, string $type=''): QueryBuilderInterface;

	/**
	 * Set a limit on the current sql statement
	 *
	 * @param int $limit
	 * @param int|bool $offset
	 * @return QueryBuilderInterface
	 */
	public function limit(int $limit, $offset=FALSE): QueryBuilderInterface;

	// --------------------------------------------------------------------------
	// ! Query Grouping Methods
	// --------------------------------------------------------------------------

	/**
	 * Adds a paren to the current query for query grouping
	 *
	 * @return QueryBuilderInterface
	 */
	public function groupStart(): QueryBuilderInterface;

	/**
	 * Adds a paren to the current query for query grouping,
	 * prefixed with 'NOT'
	 *
	 * @return QueryBuilderInterface
	 */
	public function notGroupStart(): QueryBuilderInterface;

	/**
	 * Adds a paren to the current query for query grouping,
	 * prefixed with 'OR'
	 *
	 * @return QueryBuilderInterface
	 */
	public function orGroupStart(): QueryBuilderInterface;

	/**
	 * Adds a paren to the current query for query grouping,
	 * prefixed with 'OR NOT'
	 *
	 * @return QueryBuilderInterface
	 */
	public function orNotGroupStart(): QueryBuilderInterface;

	/**
	 * Ends a query group
	 *
	 * @return QueryBuilderInterface
	 */
	public function groupEnd(): QueryBuilderInterface;

	// --------------------------------------------------------------------------
	// ! Query execution methods
	// --------------------------------------------------------------------------

	/**
	 * Select and retrieve all records from the current table, and/or
	 * execute current compiled query
	 *
	 * @param string $table
	 * @param int|bool $limit
	 * @param int|bool $offset
	 * @return PDOStatement
	 */
	public function get(string $table='', $limit=FALSE, $offset=FALSE): PDOStatement;

	/**
	 * Convenience method for get() with a where clause
	 *
	 * @param string $table
	 * @param array $where
	 * @param int|bool $limit
	 * @param int|bool $offset
	 * @return PDOStatement
	 */
	public function getWhere(string $table, $where=[], $limit=FALSE, $offset=FALSE): PDOStatement;

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
	 * @return PDOStatement
	 */
	public function insertBatch(string $table, $data=[]): PDOStatement;

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
