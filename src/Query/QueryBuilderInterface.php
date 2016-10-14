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

namespace Query;

use PDOStatement;

/**
 * Interface defining the Query Builder class
 *
 * @package Query
 * @subpackage QueryBuilder
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

	// --------------------------------------------------------------------------

	/**
	 * Selects the maximum value of a field from a query
	 *
	 * @param string $field
	 * @param string|bool $as
	 * @return QueryBuilderInterface
	 */
	public function selectMax(string $field, $as=FALSE): QueryBuilderInterface;

	// --------------------------------------------------------------------------

	/**
	 * Selects the minimum value of a field from a query
	 *
	 * @param string $field
	 * @param string|bool $as
	 * @return QueryBuilderInterface
	 */
	public function selectMin(string $field, $as=FALSE): QueryBuilderInterface;

	// --------------------------------------------------------------------------

	/**
	 * Selects the average value of a field from a query
	 *
	 * @param string $field
	 * @param string|bool $as
	 * @return QueryBuilderInterface
	 */
	public function selectAvg(string $field, $as=FALSE): QueryBuilderInterface;

	// --------------------------------------------------------------------------

	/**
	 * Selects the sum of a field from a query
	 *
	 * @param string $field
	 * @param string|bool $as
	 * @return QueryBuilderInterface
	 */
	public function selectSum(string $field, $as=FALSE): QueryBuilderInterface;

	// --------------------------------------------------------------------------q

	/**
	 * Adds the 'distinct' keyword to a query
	 *
	 * @return QueryBuilderInterface
	 */
	public function distinct(): QueryBuilderInterface;

	// --------------------------------------------------------------------------

	/**
	 * Shows the query plan for the query
	 *
	 * @return QueryBuilderInterface
	 */
	public function explain(): QueryBuilderInterface;

	// --------------------------------------------------------------------------

	/**
	 * Specify the database table to select from
	 *
	 * @param string $tblname
	 * @return QueryBuilderInterface
	 */
	public function from($tblname): QueryBuilderInterface;

	// --------------------------------------------------------------------------
	// ! 'Like' methods
	// --------------------------------------------------------------------------

	/**
	 * Creates a Like clause in the sql statement
	 *
	 * @param string $field
	 * @param mixed $val
	 * @param string $pos
	 * @return QueryBuilderInterface
	 */
	public function like($field, $val, $pos='both'): QueryBuilderInterface;

	// --------------------------------------------------------------------------

	/**
	 * Generates an OR Like clause
	 *
	 * @param string $field
	 * @param mixed $val
	 * @param string $pos
	 * @return QueryBuilderInterface
	 */
	public function orLike($field, $val, $pos='both'): QueryBuilderInterface;

	// --------------------------------------------------------------------------

	/**
	 * Generates a NOT LIKE clause
	 *
	 * @param string $field
	 * @param mixed $val
	 * @param string $pos
	 * @return QueryBuilderInterface
	 */
	public function notLike($field, $val, $pos='both'): QueryBuilderInterface;

	// --------------------------------------------------------------------------

	/**
	 * Generates a OR NOT LIKE clause
	 *
	 * @param string $field
	 * @param mixed $val
	 * @param string $pos
	 * @return QueryBuilderInterface
	 */
	public function orNotLike($field, $val, $pos='both'): QueryBuilderInterface;

	// --------------------------------------------------------------------------
	// ! Having methods
	// --------------------------------------------------------------------------

	/**
	 * Generates a 'Having' clause
	 *
	 * @param mixed $key
	 * @param mixed $val
	 * @return QueryBuilderInterface
	 */
	public function having($key, $val=[]): QueryBuilderInterface;

	// --------------------------------------------------------------------------

	/**
	 * Generates a 'Having' clause prefixed with 'OR'
	 *
	 * @param mixed $key
	 * @param mixed $val
	 * @return QueryBuilderInterface
	 */
	public function orHaving($key, $val=[]): QueryBuilderInterface;

	// --------------------------------------------------------------------------
	// ! 'Where' methods
	// --------------------------------------------------------------------------

	/**
	 * Specify condition(s) in the where clause of a query
	 * Note: this function works with key / value, or a
	 * passed array with key / value pairs
	 *
	 * @param mixed $key
	 * @param mixed $val
	 * @param bool $escape
	 * @return QueryBuilderInterface
	 */
	public function where($key, $val=[], $escape = NULL): QueryBuilderInterface;

	// --------------------------------------------------------------------------

	/**
	 * Where clause prefixed with "OR"
	 *
	 * @param string $key
	 * @param mixed $val
	 * @return QueryBuilderInterface
	 */
	public function orWhere($key, $val=[]): QueryBuilderInterface;

	// --------------------------------------------------------------------------

	/**
	 * Where clause with 'IN' statement
	 *
	 * @param mixed $field
	 * @param mixed $val
	 * @return QueryBuilderInterface
	 */
	public function whereIn($field, $val=[]): QueryBuilderInterface;

	// --------------------------------------------------------------------------

	/**
	 * Where in statement prefixed with "or"
	 *
	 * @param string $field
	 * @param mixed $val
	 * @return QueryBuilderInterface
	 */
	public function orWhereIn($field, $val=[]): QueryBuilderInterface;

	// --------------------------------------------------------------------------

	/**
	 * WHERE NOT IN (FOO) clause
	 *
	 * @param string $field
	 * @param mixed $val
	 * @return QueryBuilderInterface
	 */
	public function whereNotIn($field, $val=[]): QueryBuilderInterface;

	// --------------------------------------------------------------------------

	/**
	 * OR WHERE NOT IN (FOO) clause
	 *
	 * @param string $field
	 * @param mixed $val
	 * @return QueryBuilderInterface
	 */
	public function orWhereNotIn($field, $val=[]): QueryBuilderInterface;

	// --------------------------------------------------------------------------
	// ! Other Query Modifier methods
	// --------------------------------------------------------------------------

	/**
	 * Sets values for inserts / updates / deletes
	 *
	 * @param mixed $key
	 * @param mixed $val
	 * @return QueryBuilderInterface
	 */
	public function set($key, $val = NULL): QueryBuilderInterface;

	// --------------------------------------------------------------------------

	/**
	 * Creates a join phrase in a compiled query
	 *
	 * @param string $table
	 * @param string $condition
	 * @param string $type
	 * @return QueryBuilderInterface
	 */
	public function join($table, $condition, $type=''): QueryBuilderInterface;

	// --------------------------------------------------------------------------

	/**
	 * Group the results by the selected field(s)
	 *
	 * @param mixed $field
	 * @return QueryBuilderInterface
	 */
	public function groupBy($field): QueryBuilderInterface;

	// --------------------------------------------------------------------------

	/**
	 * Order the results by the selected field(s)
	 *
	 * @param string $field
	 * @param string $type
	 * @return QueryBuilderInterface
	 */
	public function orderBy($field, $type=""): QueryBuilderInterface;

	// --------------------------------------------------------------------------

	/**
	 * Set a limit on the current sql statement
	 *
	 * @param int $limit
	 * @param int|bool $offset
	 * @return QueryBuilderInterface
	 */
	public function limit($limit, $offset=FALSE): QueryBuilderInterface;

	// --------------------------------------------------------------------------
	// ! Query Grouping Methods
	// --------------------------------------------------------------------------

	/**
	 * Adds a paren to the current query for query grouping
	 *
	 * @return QueryBuilderInterface
	 */
	public function groupStart(): QueryBuilderInterface;

	// --------------------------------------------------------------------------

	/**
	 * Adds a paren to the current query for query grouping,
	 * prefixed with 'NOT'
	 *
	 * @return QueryBuilderInterface
	 */
	public function notGroupStart(): QueryBuilderInterface;

	// --------------------------------------------------------------------------

	/**
	 * Adds a paren to the current query for query grouping,
	 * prefixed with 'OR'
	 *
	 * @return QueryBuilderInterface
	 */
	public function orGroupStart(): QueryBuilderInterface;

	// --------------------------------------------------------------------------

	/**
	 * Adds a paren to the current query for query grouping,
	 * prefixed with 'OR NOT'
	 *
	 * @return QueryBuilderInterface
	 */
	public function orNotGroupStart(): QueryBuilderInterface;

	// --------------------------------------------------------------------------

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
	public function get($table='', $limit=FALSE, $offset=FALSE): PDOStatement;

	// --------------------------------------------------------------------------

	/**
	 * Convenience method for get() with a where clause
	 *
	 * @param string $table
	 * @param array $where
	 * @param int|bool $limit
	 * @param int|bool $offset
	 * @return PDOStatement
	 */
	public function getWhere($table, $where=[], $limit=FALSE, $offset=FALSE): PDOStatement;

	// --------------------------------------------------------------------------

	/**
	 * Retrieve the number of rows in the selected table
	 *
	 * @param string $table
	 * @return int
	 */
	public function countAll($table): int;

	// --------------------------------------------------------------------------

	/**
	 * Retrieve the number of results for the generated query - used
	 * in place of the get() method
	 *
	 * @param string $table
	 * @param bool $reset - Whether to keep the query after counting the results
	 * @return int
	 */
	public function countAllResults(string $table='', bool $reset=TRUE): int;

	// --------------------------------------------------------------------------

	/**
	 * Creates an insert clause, and executes it
	 *
	 * @param string $table
	 * @param mixed $data
	 * @return PDOStatement
	 */
	public function insert($table, $data=[]): PDOStatement;

	// --------------------------------------------------------------------------

	/**
	 * Creates and executes a batch insertion query
	 *
	 * @param string $table
	 * @param array $data
	 * @return \PDOStatement|null
	 */
	public function insertBatch($table, $data=[]);

	// --------------------------------------------------------------------------

	/**
	 * Insertion with automatic overwrite, rather than attempted duplication
	 *
	 * @param string $table
	 * @param array $data
	 * @return \PDOStatement|null
	 */
	public function replace($table, $data=[]);

	// --------------------------------------------------------------------------

	/**
	 * Creates an update clause, and executes it
	 *
	 * @param string $table
	 * @param mixed $data
	 * @return PDOStatement
	 */
	public function update($table, $data=[]): PDOStatement;

	// --------------------------------------------------------------------------

	/**
	 * Creates a batch update, and executes it.
	 * Returns the number of affected rows
	 *
	 * @param string $table
	 * @param array|object $data
	 * @param string $where
	 * @return int|null
	 */
	public function updateBatch($table, $data, $where);

	// --------------------------------------------------------------------------

	/**
	 * Deletes data from a table
	 *
	 * @param string $table
	 * @param mixed $where
	 * @return PDOStatement
	 */
	public function delete($table, $where=''): PDOStatement;

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
	public function resetQuery();
}

// End of QueryBuilderInterface.php
