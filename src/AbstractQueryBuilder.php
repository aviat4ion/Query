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
 * Abstract Class for internal implementation methods of the Query Builder
 * @package Query
 */
abstract class AbstractQueryBuilder {

	// --------------------------------------------------------------------------
	// ! Constants
	// --------------------------------------------------------------------------

	const KEY 	= 0;
	const VALUE = 1;
	const BOTH 	= 2;

	// --------------------------------------------------------------------------
	// ! SQL Clause Strings
	// --------------------------------------------------------------------------

	/**
	 * Compiled 'select' clause
	 * @var string
	 */
	protected $selectString = '';

	/**
	 * Compiled 'from' clause
	 * @var string
	 */
	protected $fromString = '';

	/**
	 * Compiled arguments for insert / update
	 * @var string
	 */
	protected $setString;

	/**
	 * Order by clause
	 * @var string
	 */
	protected $orderString;

	/**
	 * Group by clause
	 * @var string
	 */
	protected $groupString;

	// --------------------------------------------------------------------------
	// ! SQL Clause Arrays
	// --------------------------------------------------------------------------

	/**
	 * Keys for insert/update statement
	 * @var array
	 */
	protected $setArrayKeys = [];

	/**
	 * Key/val pairs for order by clause
	 * @var array
	 */
	protected $orderArray = [];

	/**
	 * Key/val pairs for group by clause
	 * @var array
	 */
	protected $groupArray = [];

	// --------------------------------------------------------------------------
	// ! Other Class vars
	// --------------------------------------------------------------------------

	/**
	 * Values to apply to prepared statements
	 * @var array
	 */
	protected $values = [];

	/**
	 * Values to apply to where clauses in prepared statements
	 * @var array
	 */
	protected $whereValues = [];

	/**
	 * Value for limit string
	 * @var string
	 */
	protected $limit;

	/**
	 * Value for offset in limit string
	 * @var integer
	 */
	protected $offset;

	/**
	 * Query component order mapping
	 * for complex select queries
	 *
	 * Format:
	 * array(
	 *		'type' => 'where',
	 *		'conjunction' => ' AND ',
	 *		'string' => 'k=?'
	 * )
	 *
	 * @var array
	 */
	protected $queryMap = [];

	/**
	 * Map for having clause
	 * @var array
	 */
	protected $havingMap;

	/**
	 * Convenience property for connection management
	 * @var string
	 */
	public $connName = "";

	/**
	 * List of queries executed
	 * @var array
	 */
	public $queries;

	/**
	 * Whether to do only an explain on the query
	 * @var boolean
	 */
	protected $explain;

	/**
	 * The current database driver
	 * @var \Query\Drivers\DriverInterface
	 */
	public $db;

	/**
	 * Query parser class instance
	 * @var QueryParser
	 */
	public $parser;

	/**
	 * Alias to driver util class
	 * @var \Query\Drivers\AbstractUtil
	 */
	public $util;

	/**
	 * Alias to driver sql class
	 * @var \Query\Drivers\SQLInterface
	 */
	public $sql;

	// --------------------------------------------------------------------------
	// Methods
	// --------------------------------------------------------------------------

	/**
	 * Set values in the class, with either an array or key value pair
	 *
	 * @param array $var
	 * @param mixed $key
	 * @param mixed $val
	 * @param int $valType
	 * @return array
	 */
	protected function _mixedSet(array &$var, $key, $val=NULL, int $valType=self::BOTH): array
	{
		$arg = (is_scalar($key) && is_scalar($val))
			? [$key => $val]
			: $key;

		foreach($arg as $k => $v)
		{
			if (in_array($valType, [self::KEY, self::VALUE]))
			{
				$var[] = ($valType === self::KEY)
					? $k
					: $v;
			}
			else
			{
				$var[$k] = $v;
			}
		}

		return $var;
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
		$field = $this->db->quoteIdent($field);

		if ( ! is_string($as))
		{
			return $field;
		}

		$as = $this->db->quoteIdent($as);
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
		$field = $this->db->quoteIdent($field);

		// Add the like string into the order map
		$like = $field. " {$like} ?";

		if ($pos == 'before')
		{
			$val = "%{$val}";
		}
		elseif ($pos == 'after')
		{
			$val = "{$val}%";
		}
		else
		{
			$val = "%{$val}%";
		}

		$conj = (empty($this->queryMap)) ? ' WHERE ' : " {$conj} ";
		$this->_appendMap($conj, $like, 'like');

		// Add to the values array
		$this->whereValues[] = $val;

		return $this;
	}

	/**
	 * Simplify building having clauses
	 *
	 * @param mixed $key
	 * @param mixed $val
	 * @param string $conj
	 * @return QueryBuilderInterface
	 */
	protected function _having($key, $val=[], string $conj='AND'): QueryBuilderInterface
	{
		$where = $this->_where($key, $val);

		// Create key/value placeholders
		foreach($where as $f => $val)
		{
			// Split each key by spaces, in case there
			// is an operator such as >, <, !=, etc.
			$fArray = explode(' ', trim($f));

			$item = $this->db->quoteIdent($fArray[0]);

			// Simple key value, or an operator
			$item .= (count($fArray) === 1) ? '=?' : " {$fArray[1]} ?";

			// Put in the having map
			$this->havingMap[] = [
				'conjunction' => ( ! empty($this->havingMap)) ? " {$conj} " : ' HAVING ',
				'string' => $item
			];
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
		$this->_mixedSet($where, $key, $val, self::BOTH);
		$this->_mixedSet($this->whereValues, $key, $val, self::VALUE);
		return $where;
	}

	/**
	 * Simplify generating where string
	 *
	 * @param mixed $key
	 * @param mixed $val
	 * @param string $defaultConj
	 * @return QueryBuilderInterface
	 */
	protected function _whereString($key, $val=[], string $defaultConj='AND'): QueryBuilderInterface
	{
		// Create key/value placeholders
		foreach($this->_where($key, $val) as $f => $val)
		{
			// Split each key by spaces, in case there
			// is an operator such as >, <, !=, etc.
			$fArray = explode(' ', trim($f));

			$item = $this->db->quoteIdent($fArray[0]);

			// Simple key value, or an operator
			$item .= (count($fArray) === 1) ? '=?' : " {$fArray[1]} ?";
			$lastItem = end($this->queryMap);

			// Determine the correct conjunction
			$conjunctionList = array_column($this->queryMap, 'conjunction');
			if (empty($this->queryMap) || ( ! regex_in_array($conjunctionList, "/^ ?\n?WHERE/i")))
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

			$this->_appendMap($conj, $item, 'where');
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
		$key = $this->db->quoteIdent($key);
		$params = array_fill(0, count($val), '?');

		foreach($val as $v)
		{
			$this->whereValues[] = $v;
		}

		$conjunction = ( ! empty($this->queryMap)) ? " {$conj} " : ' WHERE ';
		$str = $key . " {$in} (".implode(',', $params).') ';

		$this->_appendMap($conjunction, $str, 'where_in');

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
	protected function _run(string $type, string $table, $sql=NULL, $vals=NULL, bool $reset=TRUE): PDOStatement
	{
		if (is_null($sql))
		{
			$sql = $this->_compile($type, $table);
		}

		if (is_null($vals))
		{
			$vals = array_merge($this->values, (array) $this->whereValues);
		}

		$startTime = microtime(TRUE);

		$res = (empty($vals))
			? $this->db->query($sql)
			: $this->db->prepareExecute($sql, $vals);

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
	 * Add an additional set of mapping pairs to a internal map
	 *
	 * @param string $conjunction
	 * @param string $string
	 * @param string $type
	 * @return void
	 */
	protected function _appendMap(string $conjunction = '', string $string = '', string $type = '')
	{
		array_push($this->queryMap, [
			'type' => $type,
			'conjunction' => $conjunction,
			'string' => $string
		]);
	}

	/**
	 * Convert the prepared statement into readable sql
	 *
	 * @param array $vals
	 * @param string $sql
	 * @param int $totalTime
	 * @return void
	 */
	protected function _appendQuery($vals, string $sql, int $totalTime)
	{
		$evals = (is_array($vals)) ? $vals : [];
		$esql = str_replace('?', "%s", $sql);

		// Quote string values
		foreach($evals as &$v)
		{
			$v = ( ! is_numeric($v)) ? htmlentities($this->db->quote($v), ENT_NOQUOTES, 'utf-8')  : $v;
		}

		// Add the query onto the array of values to pass
		// as arguments to sprintf
		array_unshift($evals, $esql);

		// Add the interpreted query to the list of executed queries
		$this->queries[] = [
			'time' => $totalTime,
			'sql' => call_user_func_array('sprintf', $evals),
		];

		$this->queries['total_time'] += $totalTime;

		// Set the last query to get rowcounts properly
		$this->db->setLastQuery($sql);
	}

	/**
	 * Sub-method for generating sql strings
	 *
	 * @param string $type
	 * @param string $table
	 * @return string
	 */
	protected function _compileType(string $type='', string $table=''): string
	{
		switch($type)
		{
			case "insert":
				$paramCount = count($this->setArrayKeys);
				$params = array_fill(0, $paramCount, '?');
				$sql = "INSERT INTO {$table} ("
					. implode(',', $this->setArrayKeys)
					. ")\nVALUES (".implode(',', $params).')';
			break;

			case "update":
				$sql = "UPDATE {$table}\nSET {$this->setString}";
			break;

			case "replace":
				// @TODO implement
				$sql = "";
			break;

			case "delete":
				$sql = "DELETE FROM {$table}";
			break;

			// Get queries
			default:
				$sql = "SELECT * \nFROM {$this->fromString}";

				// Set the select string
				if ( ! empty($this->selectString))
				{
					// Replace the star with the selected fields
					$sql = str_replace('*', $this->selectString, $sql);
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
		$sql = $this->_compileType($type, $this->db->quoteTable($table));

		$clauses = [
			'queryMap',
			'groupString',
			'orderString',
			'havingMap',
		];

		// Set each type of subclause
		foreach($clauses as $clause)
		{
			$param = $this->$clause;
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
		if (is_numeric($this->limit))
		{
			$sql = $this->sql->limit($sql, $this->limit, $this->offset);
		}

		// See if the query plan, rather than the
		// query data should be returned
		if ($this->explain === TRUE)
		{
			$sql = $this->sql->explain($sql);
		}

		return $sql;
	}
}