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

use function is_array;
use function is_int;

use PDOStatement;

/**
 * Convenience class for creating sql queries
 */
class QueryBuilder extends QueryBuilderBase implements QueryBuilderInterface {
	// --------------------------------------------------------------------------
	// ! Select Queries
	// --------------------------------------------------------------------------

	/**
	 * Specifies rows to select in a query
	 *
	 * @param string $fields
	 * @return self
	 */
	public function select(string $fields): self
	{
		// Split fields by comma
		$fieldsArray = explode(',', $fields);
		$fieldsArray = array_map('mb_trim', $fieldsArray);

		// Split on 'As'
		foreach ($fieldsArray as $key => $field)
		{
			if (stripos($field, 'as') !== FALSE)
			{
				$fieldsArray[$key] = preg_split('` as `i', $field);
				$fieldsArray[$key] = array_map('mb_trim', $fieldsArray[$key]);
			}
		}

		// Quote the identifiers
		$safeArray = $this->driver->quoteIdent($fieldsArray);

		unset($fieldsArray);

		// Join the strings back together
		foreach ($safeArray as $i => $iValue)
		{
			if (is_array($iValue))
			{
				$safeArray[$i] = implode(' AS ', $iValue);
			}
		}

		$this->state->appendSelectString(implode(', ', $safeArray));

		return $this;
	}

	/**
	 * Selects the maximum value of a field from a query
	 *
	 * @param string $field
	 * @param string|bool $as
	 * @return self
	 */
	public function selectMax(string $field, $as=FALSE): self
	{
		// Create the select string
		$this->state->appendSelectString(' MAX'.$this->_select($field, $as));
		return $this;
	}

	/**
	 * Selects the minimum value of a field from a query
	 *
	 * @param string $field
	 * @param string|bool $as
	 * @return self
	 */
	public function selectMin(string $field, $as=FALSE): self
	{
		// Create the select string
		$this->state->appendSelectString(' MIN'.$this->_select($field, $as));
		return $this;
	}

	/**
	 * Selects the average value of a field from a query
	 *
	 * @param string $field
	 * @param string|bool $as
	 * @return self
	 */
	public function selectAvg(string $field, $as=FALSE): self
	{
		// Create the select string
		$this->state->appendSelectString(' AVG'.$this->_select($field, $as));
		return $this;
	}

	/**
	 * Selects the sum of a field from a query
	 *
	 * @param string $field
	 * @param string|bool $as
	 * @return self
	 */
	public function selectSum(string $field, $as=FALSE): self
	{
		// Create the select string
		$this->state->appendSelectString(' SUM'.$this->_select($field, $as));
		return $this;
	}

	/**
	 * Add a 'returning' clause to an insert,update, or delete query
	 *
	 * @param string $fields
	 * @return $this
	 */
	public function returning(string $fields = ''): self
	{
		$this->returning = TRUE;

		// Re-use the string select field for generating the returning type clause
		if ($fields !== '')
		{
			return $this->select($fields);
		}

		return $this;
	}

	/**
	 * Adds the 'distinct' keyword to a query
	 *
	 * @return self
	 */
	public function distinct(): self
	{
		// Prepend the keyword to the select string
		$this->state->setSelectString(' DISTINCT' . $this->state->getSelectString());
		return $this;
	}

	/**
	 * Tell the database to give you the query plan instead of result set
	 *
	 * @return self
	 */
	public function explain(): self
	{
		$this->explain = TRUE;
		return $this;
	}

	/**
	 * Specify the database table to select from
	 *
	 * @param string $tblname
	 * @return self
	 */
	public function from(string $tblname): self
	{
		// Split identifiers on spaces
		$identArray = explode(' ', \mb_trim($tblname));
		$identArray = array_map('\\mb_trim', $identArray);

		// Quote the identifiers
		$identArray[0] = $this->driver->quoteTable($identArray[0]);
		$identArray = $this->driver->quoteIdent($identArray);

		// Paste it back together
		$this->state->setFromString(implode(' ', $identArray));

		return $this;
	}

	// --------------------------------------------------------------------------
	// ! 'Like' methods
	// --------------------------------------------------------------------------

	/**
	 * Creates a Like clause in the sql statement
	 *
	 * @param string $field
	 * @param mixed $val
	 * @param string $pos
	 * @return self
	 */
	public function like(string $field, $val, string $pos='both'): self
	{
		return $this->_like($field, $val, $pos);
	}

	/**
	 * Generates an OR Like clause
	 *
	 * @param string $field
	 * @param mixed $val
	 * @param string $pos
	 * @return self
	 */
	public function orLike(string $field, $val, string $pos='both'): self
	{
		return $this->_like($field, $val, $pos, 'LIKE', 'OR');
	}

	/**
	 * Generates a NOT LIKE clause
	 *
	 * @param string $field
	 * @param mixed $val
	 * @param string $pos
	 * @return self
	 */
	public function notLike(string $field, $val, string $pos='both'): self
	{
		return $this->_like($field, $val, $pos, 'NOT LIKE');
	}

	/**
	 * Generates a OR NOT LIKE clause
	 *
	 * @param string $field
	 * @param mixed $val
	 * @param string $pos
	 * @return self
	 */
	public function orNotLike(string $field, $val, string $pos='both'): self
	{
		return $this->_like($field, $val, $pos, 'NOT LIKE', 'OR');
	}

	// --------------------------------------------------------------------------
	// ! Having methods
	// --------------------------------------------------------------------------

	/**
	 * Generates a 'Having' clause
	 *
	 * @param mixed $key
	 * @param mixed $val
	 * @return self
	 */
	public function having($key, $val=[]): self
	{
		return $this->_having($key, $val);
	}

	/**
	 * Generates a 'Having' clause prefixed with 'OR'
	 *
	 * @param mixed $key
	 * @param mixed $val
	 * @return self
	 */
	public function orHaving($key, $val=[]): self
	{
		return $this->_having($key, $val, 'OR');
	}

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
	 * @param mixed $escape
	 * @return self
	 */
	public function where($key, $val=[], $escape=NULL): self
	{
		return $this->_whereString($key, $val);
	}

	/**
	 * Where clause prefixed with "OR"
	 *
	 * @param string $key
	 * @param mixed $val
	 * @return self
	 */
	public function orWhere($key, $val=[]): self
	{
		return $this->_whereString($key, $val, 'OR');
	}

	/**
	 * Where clause with 'IN' statement
	 *
	 * @param mixed $field
	 * @param mixed $val
	 * @return self
	 */
	public function whereIn($field, $val=[]): self
	{
		return $this->_whereIn($field, $val);
	}

	/**
	 * Where in statement prefixed with "or"
	 *
	 * @param string $field
	 * @param mixed $val
	 * @return self
	 */
	public function orWhereIn($field, $val=[]): self
	{
		return $this->_whereIn($field, $val, 'IN', 'OR');
	}

	/**
	 * WHERE NOT IN (FOO) clause
	 *
	 * @param string $field
	 * @param mixed $val
	 * @return self
	 */
	public function whereNotIn($field, $val=[]): self
	{
		return $this->_whereIn($field, $val, 'NOT IN');
	}

	/**
	 * OR WHERE NOT IN (FOO) clause
	 *
	 * @param string $field
	 * @param mixed $val
	 * @return self
	 */
	public function orWhereNotIn($field, $val=[]): self
	{
		return $this->_whereIn($field, $val, 'NOT IN', 'OR');
	}

	// --------------------------------------------------------------------------
	// ! Other Query Modifier methods
	// --------------------------------------------------------------------------

	/**
	 * Sets values for inserts / updates / deletes
	 *
	 * @param mixed $key
	 * @param mixed $val
	 * @return self
	 */
	public function set($key, $val = NULL): self
	{
		if (is_scalar($key))
		{
			$pairs = [$key => $val];
		}
		else
		{
			$pairs = $key;
		}

		$keys = array_keys($pairs);
		$values = array_values($pairs);

		$this->state->appendSetArrayKeys($keys);
		$this->state->appendValues($values);

		// Use the keys of the array to make the insert/update string
		// Escape the field names
		$this->state->setSetArrayKeys(
			array_map([$this->driver, '_quote'], $this->state->getSetArrayKeys())
		);

		// Generate the "set" string
		$setString = implode('=?,', $this->state->getSetArrayKeys());
		$setString .= '=?';

		$this->state->setSetString($setString);

		return $this;
	}

	/**
	 * Creates a join phrase in a compiled query
	 *
	 * @param string $table
	 * @param string $condition
	 * @param string $type
	 * @return self
	 */
	public function join(string $table, string $condition, string $type=''): self
	{
		// Prefix and quote table name
		$tableArr = explode(' ', mb_trim($table));
		$tableArr[0] = $this->driver->quoteTable($tableArr[0]);
		$tableArr = $this->driver->quoteIdent($tableArr);
		$table = implode(' ', $tableArr);

		// Parse out the join condition
		$parsedCondition = $this->parser->compileJoin($condition);
		$condition = $table . ' ON ' . $parsedCondition;

		$this->state->appendMap("\n" . strtoupper($type) . ' JOIN ', $condition, MapType::JOIN);

		return $this;
	}

	/**
	 * Group the results by the selected field(s)
	 *
	 * @param mixed $field
	 * @return self
	 */
	public function groupBy($field): self
	{
		if ( ! is_scalar($field))
		{
			$newGroupArray = array_merge(
				$this->state->getGroupArray(),
				array_map([$this->driver, 'quoteIdent'], $field)
			);
			$this->state->setGroupArray($newGroupArray);
		}
		else
		{
			$this->state->appendGroupArray($this->driver->quoteIdent($field));
		}

		$this->state->setGroupString(' GROUP BY ' . implode(',', $this->state->getGroupArray()));

		return $this;
	}

	/**
	 * Order the results by the selected field(s)
	 *
	 * @param string $field
	 * @param string $type
	 * @return self
	 */
	public function orderBy(string $field, string $type=''): self
	{
		// When ordering by random, do an ascending order if the driver
		// doesn't support random ordering
		if (stripos($type, 'rand') !== FALSE)
		{
			$rand = $this->driver->getSql()->random();
			$type = $rand ?? 'ASC';
		}

		// Set fields for later manipulation
		$field = $this->driver->quoteIdent($field);
		$this->state->setOrderArray($field, $type);

		$orderClauses = [];

		// Flatten key/val pairs into an array of space-separated pairs
		foreach($this->state->getOrderArray() as $k => $v)
		{
			$orderClauses[] = $k . ' ' . strtoupper($v);
		}

		// Set the final string
		$orderString =  ! isset($rand)
			? "\nORDER BY ".implode(', ', $orderClauses)
			: "\nORDER BY".$rand;

		$this->state->setOrderString($orderString);

		return $this;
	}

	/**
	 * Set a limit on the current sql statement
	 *
	 * @param int $limit
	 * @param int|null $offset
	 * @return self
	 */
	public function limit(int $limit, ?int $offset=NULL): self
	{
		$this->state->setLimit($limit);
		$this->state->setOffset($offset);

		return $this;
	}

	// --------------------------------------------------------------------------
	// ! Query Grouping Methods
	// --------------------------------------------------------------------------

	/**
	 * Adds a paren to the current query for query grouping
	 *
	 * @return self
	 */
	public function groupStart(): self
	{
		$conj = empty($this->state->getQueryMap()) ? ' WHERE ' : ' ';

		$this->state->appendMap($conj, '(', MapType::GROUP_START);

		return $this;
	}

	/**
	 * Adds a paren to the current query for query grouping,
	 * prefixed with 'NOT'
	 *
	 * @return self
	 */
	public function notGroupStart(): self
	{
		$conj = empty($this->state->getQueryMap()) ? ' WHERE ' : ' AND ';

		$this->state->appendMap($conj, ' NOT (', MapType::GROUP_START);

		return $this;
	}

	/**
	 * Adds a paren to the current query for query grouping,
	 * prefixed with 'OR'
	 *
	 * @return self
	 */
	public function orGroupStart(): self
	{
		$this->state->appendMap('', ' OR (', MapType::GROUP_START);

		return $this;
	}

	/**
	 * Adds a paren to the current query for query grouping,
	 * prefixed with 'OR NOT'
	 *
	 * @return self
	 */
	public function orNotGroupStart(): self
	{
		$this->state->appendMap('', ' OR NOT (', MapType::GROUP_START);

		return $this;
	}

	/**
	 * Ends a query group
	 *
	 * @return self
	 */
	public function groupEnd(): self
	{
		$this->state->appendMap('', ')', MapType::GROUP_END);

		return $this;
	}

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
	public function get(string $table='', ?int $limit=NULL, ?int $offset=NULL): PDOStatement
	{
		// Set the table
		if ( ! empty($table))
		{
			$this->from($table);
		}

		// Set the limit, if it exists
		if (is_int($limit))
		{
			$this->limit($limit, $offset);
		}

		return $this->_run('get', $table);
	}

	/**
	 * Convenience method for get() with a where clause
	 *
	 * @param string $table
	 * @param mixed $where
	 * @param int|null $limit
	 * @param int|null $offset
	 * @return PDOStatement
	 */
	public function getWhere(string $table, $where=[], ?int $limit=NULL, ?int $offset=NULL): PDOStatement
	{
		// Create the where clause
		$this->where($where);

		// Return the result
		return $this->get($table, $limit, $offset);
	}

	/**
	 * Retrieve the number of rows in the selected table
	 *
	 * @param string $table
	 * @return int
	 */
	public function countAll(string $table): int
	{
		$sql = 'SELECT * FROM '.$this->driver->quoteTable($table);
		$res = $this->driver->query($sql);
		return (int) count($res->fetchAll());
	}

	/**
	 * Retrieve the number of results for the generated query - used
	 * in place of the get() method
	 *
	 * @param string $table
	 * @param boolean $reset
	 * @return int
	 */
	public function countAllResults(string $table='', bool $reset = TRUE): int
	{
		// Set the table
		if ( ! empty($table))
		{
			$this->from($table);
		}

		$result = $this->_run(QueryType::SELECT, $table, NULL, NULL, $reset);
		$rows = $result->fetchAll();

		return (int) count($rows);
	}

	/**
	 * Creates an insert clause, and executes it
	 *
	 * @param string $table
	 * @param mixed $data
	 * @return PDOStatement
	 */
	public function insert(string $table, $data=[]): PDOStatement
	{
		if ( ! empty($data))
		{
			$this->set($data);
		}

		return $this->_run(QueryType::INSERT, $table);
	}

	/**
	 * Creates and executes a batch insertion query
	 *
	 * @param string $table
	 * @param array $data
	 * @return PDOStatement
	 */
	public function insertBatch(string $table, $data=[]): ?PDOStatement
	{
		// Get the generated values and sql string
		[$sql, $data] = $this->driver->insertBatch($table, $data);

		return $sql !== NULL
			? $this->_run('', $table, $sql, $data)
			: NULL;
	}

	/**
	 * Creates an update clause, and executes it
	 *
	 * @param string $table
	 * @param mixed $data
	 * @return PDOStatement
	 */
	public function update(string $table, $data=[]): PDOStatement
	{
		if ( ! empty($data))
		{
			$this->set($data);
		}

		return $this->_run(QueryType::UPDATE, $table);
	}

	/**
	 * Creates a batch update, and executes it.
	 * Returns the number of affected rows
	 *
	 * @param string $table
	 * @param array $data
	 * @param string $where
	 * @return int|null
	 */
	public function updateBatch(string $table, array $data, string $where): ?int
	{
		if (empty($table) || empty($data) || empty($where))
		{
			return NULL;
		}

		// Get the generated values and sql string
		[$sql, $data, $affectedRows] = $this->driver->updateBatch($table, $data, $where);

		$this->_run('', $table, $sql, $data);
		return $affectedRows;
	}

	/**
	 * Deletes data from a table
	 *
	 * @param string $table
	 * @param mixed $where
	 * @return PDOStatement
	 */
	public function delete(string $table, $where=''): PDOStatement
	{
		// Set the where clause
		if ( ! empty($where))
		{
			$this->where($where);
		}

		return $this->_run(QueryType::DELETE, $table);
	}

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
	public function getCompiledSelect(string $table='', bool $reset=TRUE): string
	{
		// Set the table
		if ( ! empty($table))
		{
			$this->from($table);
		}

		return $this->_getCompile(QueryType::SELECT, $table, $reset);
	}

	/**
	 * Returns the generated 'insert' sql query
	 *
	 * @param string $table
	 * @param bool $reset
	 * @return string
	 */
	public function getCompiledInsert(string $table, bool $reset=TRUE): string
	{
		return $this->_getCompile(QueryType::INSERT, $table, $reset);
	}

	/**
	 * Returns the generated 'update' sql query
	 *
	 * @param string $table
	 * @param bool $reset
	 * @return string
	 */
	public function getCompiledUpdate(string $table='', bool $reset=TRUE): string
	{
		return $this->_getCompile(QueryType::UPDATE, $table, $reset);
	}

	/**
	 * Returns the generated 'delete' sql query
	 *
	 * @param string $table
	 * @param bool $reset
	 * @return string
	 */
	public function getCompiledDelete(string $table='', bool $reset=TRUE): string
	{
		return $this->_getCompile(QueryType::DELETE, $table, $reset);
	}
}
