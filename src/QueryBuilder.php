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
namespace Query;

use function is_array;
use function regexInArray;

use BadMethodCallException;
use PDO;
use PDOStatement;
use Query\Drivers\DriverInterface;

/**
 * Convenience class for creating sql queries
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
class QueryBuilder implements QueryBuilderInterface {

	/**
	 * Convenience property for connection management
	 * @var string
	 */
	public $connName = '';

	/**
	 * List of queries executed
	 * @var array
	 */
	public $queries = [
		'total_time' => 0
	];

	/**
	 * Whether to do only an explain on the query
	 * @var bool
	 */
	protected $explain = FALSE;

	/**
	 * Whether to return data from a modification query
	 * @var bool
	 */
	protected $returning = FALSE;

	/**
	 * The current database driver
	 * @var DriverInterface
	 */
	protected $driver;

	/**
	 * Query parser class instance
	 * @var QueryParser
	 */
	protected $parser;

	/**
	 * Query Builder state
	 * @var State
	 */
	protected $state;

	// --------------------------------------------------------------------------
	// ! Methods
	// --------------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @param DriverInterface $driver
	 * @param QueryParser $parser
	 */
	public function __construct(DriverInterface $driver, QueryParser $parser)
	{
		// Inject driver and parser
		$this->driver = $driver;
		$this->parser = $parser;

		// Create new State object
		$this->state = new State();
	}

	/**
	 * Destructor
	 * @codeCoverageIgnore
	 */
	public function __destruct()
	{
		$this->driver = NULL;
	}

	/**
	 * Calls a function further down the inheritance chain.
	 * 'Implements' methods on the driver object
	 *
	 * @param string $name
	 * @param array $params
	 * @return mixed
	 * @throws BadMethodCallException
	 */
	public function __call(string $name, array $params)
	{
		if (method_exists($this->driver, $name))
		{
			return $this->driver->$name(...$params);
		}

		throw new BadMethodCallException('Method does not exist');
	}

	// --------------------------------------------------------------------------
	// ! Select Queries
	// --------------------------------------------------------------------------

	/**
	 * Specifies rows to select in a query
	 *
	 * @param string $fields
	 * @return QueryBuilderInterface
	 */
	public function select(string $fields): QueryBuilderInterface
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
	 * @return QueryBuilderInterface
	 */
	public function selectMax(string $field, $as=FALSE): QueryBuilderInterface
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
	 * @return QueryBuilderInterface
	 */
	public function selectMin(string $field, $as=FALSE): QueryBuilderInterface
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
	 * @return QueryBuilderInterface
	 */
	public function selectAvg(string $field, $as=FALSE): QueryBuilderInterface
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
	 * @return QueryBuilderInterface
	 */
	public function selectSum(string $field, $as=FALSE): QueryBuilderInterface
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
	public function returning(string $fields = ''): QueryBuilderInterface
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
	 * @return QueryBuilderInterface
	 */
	public function distinct(): QueryBuilderInterface
	{
		// Prepend the keyword to the select string
		$this->state->setSelectString(' DISTINCT' . $this->state->getSelectString());
		return $this;
	}

	/**
	 * Tell the database to give you the query plan instead of result set
	 *
	 * @return QueryBuilderInterface
	 */
	public function explain(): QueryBuilderInterface
	{
		$this->explain = TRUE;
		return $this;
	}

	/**
	 * Specify the database table to select from
	 *
	 * @param string $tblname
	 * @return QueryBuilderInterface
	 */
	public function from(string $tblname): QueryBuilderInterface
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
	 * @return QueryBuilderInterface
	 */
	public function like(string $field, $val, string $pos='both'): QueryBuilderInterface
	{
		return $this->_like($field, $val, $pos);
	}

	/**
	 * Generates an OR Like clause
	 *
	 * @param string $field
	 * @param mixed $val
	 * @param string $pos
	 * @return QueryBuilderInterface
	 */
	public function orLike(string $field, $val, string $pos='both'): QueryBuilderInterface
	{
		return $this->_like($field, $val, $pos, 'LIKE', 'OR');
	}

	/**
	 * Generates a NOT LIKE clause
	 *
	 * @param string $field
	 * @param mixed $val
	 * @param string $pos
	 * @return QueryBuilderInterface
	 */
	public function notLike(string $field, $val, string $pos='both'): QueryBuilderInterface
	{
		return $this->_like($field, $val, $pos, 'NOT LIKE');
	}

	/**
	 * Generates a OR NOT LIKE clause
	 *
	 * @param string $field
	 * @param mixed $val
	 * @param string $pos
	 * @return QueryBuilderInterface
	 */
	public function orNotLike(string $field, $val, string $pos='both'): QueryBuilderInterface
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
	 * @return QueryBuilderInterface
	 */
	public function having($key, $val=[]): QueryBuilderInterface
	{
		return $this->_having($key, $val);
	}

	/**
	 * Generates a 'Having' clause prefixed with 'OR'
	 *
	 * @param mixed $key
	 * @param mixed $val
	 * @return QueryBuilderInterface
	 */
	public function orHaving($key, $val=[]): QueryBuilderInterface
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
	 * @return QueryBuilderInterface
	 */
	public function where($key, $val=[], $escape=NULL): QueryBuilderInterface
	{
		return $this->_whereString($key, $val);
	}

	/**
	 * Where clause prefixed with "OR"
	 *
	 * @param string $key
	 * @param mixed $val
	 * @return QueryBuilderInterface
	 */
	public function orWhere($key, $val=[]): QueryBuilderInterface
	{
		return $this->_whereString($key, $val, 'OR');
	}

	/**
	 * Where clause with 'IN' statement
	 *
	 * @param mixed $field
	 * @param mixed $val
	 * @return QueryBuilderInterface
	 */
	public function whereIn($field, $val=[]): QueryBuilderInterface
	{
		return $this->_whereIn($field, $val);
	}

	/**
	 * Where in statement prefixed with "or"
	 *
	 * @param string $field
	 * @param mixed $val
	 * @return QueryBuilderInterface
	 */
	public function orWhereIn($field, $val=[]): QueryBuilderInterface
	{
		return $this->_whereIn($field, $val, 'IN', 'OR');
	}

	/**
	 * WHERE NOT IN (FOO) clause
	 *
	 * @param string $field
	 * @param mixed $val
	 * @return QueryBuilderInterface
	 */
	public function whereNotIn($field, $val=[]): QueryBuilderInterface
	{
		return $this->_whereIn($field, $val, 'NOT IN');
	}

	/**
	 * OR WHERE NOT IN (FOO) clause
	 *
	 * @param string $field
	 * @param mixed $val
	 * @return QueryBuilderInterface
	 */
	public function orWhereNotIn($field, $val=[]): QueryBuilderInterface
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
	 * @return QueryBuilderInterface
	 */
	public function set($key, $val = NULL): QueryBuilderInterface
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
	 * @return QueryBuilderInterface
	 */
	public function join(string $table, string $condition, string $type=''): QueryBuilderInterface
	{
		// Prefix and quote table name
		$table = explode(' ', mb_trim($table));
		$table[0] = $this->driver->quoteTable($table[0]);
		$table = $this->driver->quoteIdent($table);
		$table = implode(' ', $table);

		// Parse out the join condition
		$parsedCondition = $this->parser->compileJoin($condition);
		$condition = $table . ' ON ' . $parsedCondition;

		$this->state->appendMap("\n" . strtoupper($type) . ' JOIN ', $condition, 'join');

		return $this;
	}

	/**
	 * Group the results by the selected field(s)
	 *
	 * @param mixed $field
	 * @return QueryBuilderInterface
	 */
	public function groupBy($field): QueryBuilderInterface
	{
		if ( ! is_scalar($field))
		{
			$newGroupArray = array_map([$this->driver, 'quoteIdent'], $field);
			$this->state->setGroupArray(
				array_merge($this->state->getGroupArray(), $newGroupArray)
			);
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
	 * @return QueryBuilderInterface
	 */
	public function orderBy(string $field, string $type=''): QueryBuilderInterface
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
	 * @param int|bool $offset
	 * @return QueryBuilderInterface
	 */
	public function limit(int $limit, $offset=FALSE): QueryBuilderInterface
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
	 * @return QueryBuilderInterface
	 */
	public function groupStart(): QueryBuilderInterface
	{
		$conj = empty($this->state->getQueryMap()) ? ' WHERE ' : ' ';

		$this->state->appendMap($conj, '(', 'group_start');

		return $this;
	}

	/**
	 * Adds a paren to the current query for query grouping,
	 * prefixed with 'NOT'
	 *
	 * @return QueryBuilderInterface
	 */
	public function notGroupStart(): QueryBuilderInterface
	{
		$conj = empty($this->state->getQueryMap()) ? ' WHERE ' : ' AND ';

		$this->state->appendMap($conj, ' NOT (', 'group_start');

		return $this;
	}

	/**
	 * Adds a paren to the current query for query grouping,
	 * prefixed with 'OR'
	 *
	 * @return QueryBuilderInterface
	 */
	public function orGroupStart(): QueryBuilderInterface
	{
		$this->state->appendMap('', ' OR (', 'group_start');

		return $this;
	}

	/**
	 * Adds a paren to the current query for query grouping,
	 * prefixed with 'OR NOT'
	 *
	 * @return QueryBuilderInterface
	 */
	public function orNotGroupStart(): QueryBuilderInterface
	{
		$this->state->appendMap('', ' OR NOT (', 'group_start');

		return $this;
	}

	/**
	 * Ends a query group
	 *
	 * @return QueryBuilderInterface
	 */
	public function groupEnd(): QueryBuilderInterface
	{
		$this->state->appendMap('', ')', 'group_end');

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
	 * @param int|bool $limit
	 * @param int|bool $offset
	 * @return PDOStatement
	 */
	public function get(string $table='', $limit=FALSE, $offset=FALSE): PDOStatement
	{
		// Set the table
		if ( ! empty($table))
		{
			$this->from($table);
		}

		// Set the limit, if it exists
		if (\is_int($limit))
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
	 * @param int|bool $limit
	 * @param int|bool $offset
	 * @return PDOStatement
	 */
	public function getWhere(string $table, $where=[], $limit=FALSE, $offset=FALSE): PDOStatement
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
	public function insertBatch(string $table, $data=[]): PDOStatement
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

	// --------------------------------------------------------------------------
	// ! Miscellaneous Methods
	// --------------------------------------------------------------------------

	/**
	 * Clear out the class variables, so the next query can be run
	 *
	 * @return void
	 */
	public function resetQuery(): void
	{
		$this->state = new State();
		$this->explain = FALSE;
		$this->returning = FALSE;
	}

	/**
	 * Method to simplify select_ methods
	 *
	 * @param string $field
	 * @param string|bool $as
	 * @return string
	 */
	protected function _select(string $field, $as = FALSE): string
	{
		// Escape the identifiers
		$field = $this->driver->quoteIdent($field);

		if ( ! \is_string($as))
		{
			// @codeCoverageIgnoreStart
			return $field;
			// @codeCoverageIgnoreEnd
		}

		$as = $this->driver->quoteIdent($as);
		return "({$field}) AS {$as} ";
	}

	/**
	 * Helper function for returning sql strings
	 *
	 * @param string $type
	 * @param string $table
	 * @param bool $reset
	 * @return string
	 */
	protected function _getCompile(string $type, string $table, bool $reset): string
	{
		$sql = $this->_compile($type, $table);

		// Reset the query builder for the next query
		if ($reset)
		{
			$this->resetQuery();
		}

		return $sql;
	}

	/**
	 * Simplify 'like' methods
	 *
	 * @param string $field
	 * @param mixed $val
	 * @param string $pos
	 * @param string $like
	 * @param string $conj
	 * @return QueryBuilderInterface
	 */
	protected function _like(string $field, $val, string $pos, string $like='LIKE', string $conj='AND'): QueryBuilderInterface
	{
		$field = $this->driver->quoteIdent($field);

		// Add the like string into the order map
		$like = $field. " {$like} ?";

		if ($pos === 'before')
		{
			$val = "%{$val}";
		}
		elseif ($pos === 'after')
		{
			$val = "{$val}%";
		}
		else
		{
			$val = "%{$val}%";
		}

		$conj = empty($this->state->getQueryMap()) ? ' WHERE ' : " {$conj} ";
		$this->state->appendMap($conj, $like, 'like');

		// Add to the values array
		$this->state->appendWhereValues($val);

		return $this;
	}

	/**
	 * Simplify building having clauses
	 *
	 * @param mixed $key
	 * @param mixed $values
	 * @param string $conj
	 * @return QueryBuilderInterface
	 */
	protected function _having($key, $values=[], string $conj='AND'): QueryBuilderInterface
	{
		$where = $this->_where($key, $values);

		// Create key/value placeholders
		foreach($where as $f => $val)
		{
			// Split each key by spaces, in case there
			// is an operator such as >, <, !=, etc.
			$fArray = explode(' ', trim($f));

			$item = $this->driver->quoteIdent($fArray[0]);

			// Simple key value, or an operator
			$item .= (count($fArray) === 1) ? '=?' : " {$fArray[1]} ?";

			// Put in the having map
			$this->state->appendHavingMap([
				'conjunction' => empty($this->state->getHavingMap())
					? ' HAVING '
					: " {$conj} ",
				'string' => $item
			]);
		}

		return $this;
	}

	/**
	 * Do all the redundant stuff for where/having type methods
	 *
	 * @param mixed $key
	 * @param mixed $val
	 * @return array
	 */
	protected function _where($key, $val=[]): array
	{
		$where = [];
		$pairs = [];

		if (is_scalar($key))
		{
			$pairs[$key] = $val;
		}
		else
		{
			$pairs = $key;
		}

		foreach($pairs as $k => $v)
		{
			$where[$k] = $v;
			$this->state->appendWhereValues($v);
		}

		return $where;
	}

	/**
	 * Simplify generating where string
	 *
	 * @param mixed $key
	 * @param mixed $values
	 * @param string $defaultConj
	 * @return QueryBuilderInterface
	 */
	protected function _whereString($key, $values=[], string $defaultConj='AND'): QueryBuilderInterface
	{
		// Create key/value placeholders
		foreach($this->_where($key, $values) as $f => $val)
		{
			$queryMap = $this->state->getQueryMap();

			// Split each key by spaces, in case there
			// is an operator such as >, <, !=, etc.
			$fArray = explode(' ', trim($f));

			$item = $this->driver->quoteIdent($fArray[0]);

			// Simple key value, or an operator
			$item .= (count($fArray) === 1) ? '=?' : " {$fArray[1]} ?";
			$lastItem = end($queryMap);

			// Determine the correct conjunction
			$conjunctionList = array_column($queryMap, 'conjunction');
			if (empty($queryMap) || ( ! regexInArray($conjunctionList, "/^ ?\n?WHERE/i")))
			{
				$conj = "\nWHERE ";
			}
			elseif ($lastItem['type'] === 'group_start')
			{
				$conj = '';
			}
			else
			{
				$conj = " {$defaultConj} ";
			}

			$this->state->appendMap($conj, $item, 'where');
		}

		return $this;
	}

	/**
	 * Simplify where_in methods
	 *
	 * @param mixed $key
	 * @param mixed $val
	 * @param string $in - The (not) in fragment
	 * @param string $conj - The where in conjunction
	 * @return QueryBuilderInterface
	 */
	protected function _whereIn($key, $val=[], string $in='IN', string $conj='AND'): QueryBuilderInterface
	{
		$key = $this->driver->quoteIdent($key);
		$params = array_fill(0, count($val), '?');
		$this->state->appendWhereValues($val);

		$conjunction =  empty($this->state->getQueryMap()) ? ' WHERE ' : " {$conj} ";
		$str = $key . " {$in} (".implode(',', $params).') ';

		$this->state->appendMap($conjunction, $str, 'where_in');

		return $this;
	}

	/**
	 * Executes the compiled query
	 *
	 * @param string $type
	 * @param string $table
	 * @param string $sql
	 * @param array|null $vals
	 * @param boolean $reset
	 * @return PDOStatement
	 */
	protected function _run(string $type, string $table, string $sql=NULL, array $vals=NULL, bool $reset=TRUE): PDOStatement
	{
		if ($sql === NULL)
		{
			$sql = $this->_compile($type, $table);
		}

		if ($vals === NULL)
		{
			$vals = array_merge($this->state->getValues(), $this->state->getWhereValues());
		}

		$startTime = microtime(TRUE);

		$res = empty($vals)
			? $this->driver->query($sql)
			: $this->driver->prepareExecute($sql, $vals);

		$endTime = microtime(TRUE);
		$totalTime = number_format($endTime - $startTime, 5);

		// Add this query to the list of executed queries
		$this->_appendQuery($vals, $sql, (int) $totalTime);

		// Reset class state for next query
		if ($reset)
		{
			$this->resetQuery();
		}

		return $res;
	}

	/**
	 * Convert the prepared statement into readable sql
	 *
	 * @param array $values
	 * @param string $sql
	 * @param int $totalTime
	 * @return void
	 */
	protected function _appendQuery(array $values, string $sql, int $totalTime): void
	{
		$evals = is_array($values) ? $values : [];
		$esql = str_replace('?', '%s', $sql);

		// Quote string values
		foreach($evals as &$v)
		{
			$v = ( ! is_numeric($v))
				? htmlentities($this->driver->quote($v), ENT_NOQUOTES, 'utf-8')
				: $v;
		}
		unset($v);

		// Add the query onto the array of values to pass
		// as arguments to sprintf
		array_unshift($evals, $esql);

		// Add the interpreted query to the list of executed queries
		$this->queries[] = [
			'time' => $totalTime,
			'sql' => sprintf(...$evals)
		];

		$this->queries['total_time'] += $totalTime;

		// Set the last query to get rowcounts properly
		$this->driver->setLastQuery($sql);
	}

	/**
	 * Sub-method for generating sql strings
	 *
	 * @codeCoverageIgnore
	 * @param string $type
	 * @param string $table
	 * @return string
	 */
	protected function _compileType(string $type=QueryType::SELECT, string $table=''): string
	{
		$setArrayKeys = $this->state->getSetArrayKeys();
		switch($type)
		{
			case QueryType::INSERT:
				$paramCount = count($setArrayKeys);
				$params = array_fill(0, $paramCount, '?');
				$sql = "INSERT INTO {$table} ("
					. implode(',', $setArrayKeys)
					. ")\nVALUES (".implode(',', $params).')';
			break;

			case QueryType::UPDATE:
				$setString = $this->state->getSetString();
				$sql = "UPDATE {$table}\nSET {$setString}";
			break;

			case QueryType::DELETE:
				$sql = "DELETE FROM {$table}";
			break;

			case QueryType::SELECT:
			default:
				$fromString = $this->state->getFromString();
				$selectString = $this->state->getSelectString();

				$sql = "SELECT * \nFROM {$fromString}";

				// Set the select string
				if ( ! empty($selectString))
				{
					// Replace the star with the selected fields
					$sql = str_replace('*', $selectString, $sql);
				}
			break;
		}

		return $sql;
	}

	/**
	 * String together the sql statements for sending to the db
	 *
	 * @param string $type
	 * @param string $table
	 * @return string
	 */
	protected function _compile(string $type='', string $table=''): string
	{
		// Get the base clause for the query
		$sql = $this->_compileType($type, $this->driver->quoteTable($table));

		$clauses = [
			'queryMap',
			'groupString',
			'orderString',
			'havingMap',
		];

		// Set each type of subclause
		foreach($clauses as $clause)
		{
			$func = 'get' . ucFirst($clause);
			$param = $this->state->$func();
			if (is_array($param))
			{
				foreach($param as $q)
				{
					$sql .= $q['conjunction'] . $q['string'];
				}
			}
			else
			{
				$sql .= $param;
			}
		}

		// Set the limit via the class variables
		$limit = $this->state->getLimit();
		if (is_numeric($limit))
		{
			$sql = $this->driver->getSql()->limit($sql, $limit, $this->state->getOffset());
		}

		// Set the returning clause, if applicable
		$sql = $this->_compileReturning($sql, $type);

		// See if the query plan, rather than the
		// query data should be returned
		if ($this->explain === TRUE)
		{
			$sql = $this->driver->getSql()->explain($sql);
		}

		return $sql;
	}

	/**
	 * Generate returning clause of query
	 *
	 * @param string $sql
	 * @param string $type
	 * @return string
	 */
	protected function _compileReturning(string $sql, string $type): string
	{
		if ($this->returning === FALSE)
		{
			return $sql;
		}

		$rawSelect = $this->state->getSelectString();
		$selectString = ($rawSelect === '') ? '*' : $rawSelect;
		$returningSQL = $this->driver->returning($sql, $selectString);

		if ($returningSQL === $sql)
		{
			// If the driver doesn't support the returning clause, it returns the original query.
			// Fake the same result with a transaction and a select query
			if ( ! $this->inTransaction())
			{
				$this->beginTransaction();
			}

			// Generate the appropriate select query for the returning clause fallback
			switch ($type)
			{
				case QueryType::INSERT:
					// @TODO figure out a good response for insert query
				break;

				case QueryType::UPDATE:
					// @TODO figure out a good response for update query
				break;

				case QueryType::DELETE:
					// @TODO Figure out a good response for delete query
				break;
			}
		}

		return $returningSQL;
	}
}
