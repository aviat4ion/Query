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
namespace Query;

use function regexInArray;

use BadMethodCallException;
use PDO;
use PDOStatement;
use Query\Drivers\DriverInterface;

/**
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
class QueryBuilderBase {

	/**
	 * Convenience property for connection management
	 */
	public string $connName = '';

	/**
	 * List of queries executed
	 */
	public array $queries = [
		'total_time' => 0
	];

	/**
	 * Whether to do only an explain on the query
	 */
	protected bool $explain = FALSE;

	/**
	 * Whether to return data from a modification query
	 */
	protected bool $returning = FALSE;

	/**
	 * Query Builder state
	 */
	protected State $state;

	// --------------------------------------------------------------------------
	// ! Methods
	// --------------------------------------------------------------------------
	/**
	 * Constructor
	 */
	public function __construct(protected ?DriverInterface $driver, protected QueryParser $parser)
	{
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

	/**
	 * Clear out the class variables, so the next query can be run
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
	 * @param string|bool $as
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
	 * @param mixed $val
	 */
	protected function _like(string $field, $val, string $pos, string $like = 'LIKE', string $conj = 'AND'): self
	{
		$field = $this->driver->quoteIdent($field);

		// Add the like string into the order map
		$like = $field . " {$like} ?";

		if ($pos === LikeType::BEFORE)
		{
			$val = "%{$val}";
		}
		elseif ($pos === LikeType::AFTER)
		{
			$val = "{$val}%";
		}
		else
		{
			$val = "%{$val}%";
		}

		$conj = empty($this->state->getQueryMap()) ? ' WHERE ' : " {$conj} ";
		$this->state->appendMap($conj, $like, MapType::LIKE);

		// Add to the values array
		$this->state->appendWhereValues($val);

		return $this;
	}

	/**
	 * Simplify building having clauses
	 *
	 * @param mixed $key
	 * @param mixed $values
	 */
	protected function _having($key, $values = [], string $conj = 'AND'): self
	{
		$where = $this->_where($key, $values);

		// Create key/value placeholders
		foreach ($where as $f => $val)
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
	 */
	protected function _where($key, $val = []): array
	{
		$where = [];
		$pairs = [];

		if (is_scalar($key))
		{
			$pairs[$key] = $val;
		} else
		{
			$pairs = $key;
		}

		foreach ($pairs as $k => $v)
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
	 */
	protected function _whereString($key, $values = [], string $defaultConj = 'AND'): self
	{
		// Create key/value placeholders
		foreach ($this->_where($key, $values) as $f => $val)
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
			} elseif ($lastItem['type'] === 'group_start')
			{
				$conj = '';
			} else
			{
				$conj = " {$defaultConj} ";
			}

			$this->state->appendMap($conj, $item, MapType::WHERE);
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
	 */
	protected function _whereIn($key, $val = [], string $in = 'IN', string $conj = 'AND'): self
	{
		$key = $this->driver->quoteIdent($key);
		$params = array_fill(0, is_countable($val) ? count($val) : 0, '?');
		$this->state->appendWhereValues($val);

		$conjunction = empty($this->state->getQueryMap()) ? ' WHERE ' : " {$conj} ";
		$str = $key . " {$in} (" . implode(',', $params) . ') ';

		$this->state->appendMap($conjunction, $str, MapType::WHERE_IN);

		return $this;
	}

	/**
	 * Executes the compiled query
	 *
	 * @param array|null $vals
	 */
	protected function _run(string $type, string $table, string $sql = NULL, array $vals = NULL, bool $reset = TRUE): PDOStatement
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
		$this->_appendQuery($vals, $sql, (int)$totalTime);

		// Reset class state for next query
		if ($reset)
		{
			$this->resetQuery();
		}

		return $res;
	}

	/**
	 * Convert the prepared statement into readable sql
	 */
	protected function _appendQuery(array $values, string $sql, int $totalTime): void
	{
		$evals = is_array($values) ? $values : [];
		$esql = str_replace('?', '%s', $sql);

		// Quote string values
		foreach ($evals as &$v)
		{
			$v = ( is_numeric($v))
				? $v
				: htmlentities($this->driver->quote($v), ENT_NOQUOTES, 'utf-8');
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
	 */
	protected function _compileType(string $type = QueryType::SELECT, string $table = ''): string
	{
		$setArrayKeys = $this->state->getSetArrayKeys();
		switch ($type)
		{
			case QueryType::INSERT:
				$paramCount = is_countable($setArrayKeys) ? count($setArrayKeys) : 0;
				$params = array_fill(0, $paramCount, '?');
				$sql = "INSERT INTO {$table} ("
					. implode(',', $setArrayKeys)
					. ")\nVALUES (" . implode(',', $params) . ')';
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
	 */
	protected function _compile(string $type = '', string $table = ''): string
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
		foreach ($clauses as $clause)
		{
			$func = 'get' . ucFirst($clause);
			$param = $this->state->$func();
			if (is_array($param))
			{
				foreach ($param as $q)
				{
					$sql .= $q['conjunction'] . $q['string'];
				}
			} else
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
			// @TODO figure out how to do a proper fallback
			switch ($type)
			{
				case QueryType::INSERT:

				case QueryType::UPDATE:

				case QueryType::INSERT_BATCH:
				case QueryType::UPDATE_BATCH:

				default:
					// On Delete queries, what would we return?
					break;
			}
		}

		return $returningSQL;
	}
}