<?php
/**
 * Query
 *
 * Free Query Builder / Database Abstraction Layer
 *
 * @package		Query
 * @author		Timothy J. Warren
 * @copyright	Copyright (c) 2012 - 2014
 * @link 		https://github.com/aviat4ion/Query
 * @license		http://philsturgeon.co.uk/code/dbad-license
 */

// --------------------------------------------------------------------------

namespace Query;

// --------------------------------------------------------------------------

/**
 * Abstract Class for internal implementation methods of the Query Builder
 * @package Query
 */
abstract class Abstract_Query_Builder {

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
	protected $select_string = '';

	/**
	 * Compiled 'from' clause
	 * @var string
	 */
	protected $from_string;

	/**
	 * Compiled arguments for insert / update
	 * @var string
	 */
	protected $set_string;

	/**
	 * Order by clause
	 * @var string
	 */
	protected $order_string;

	/**
	 * Group by clause
	 * @var string
	 */
	protected $group_string;

	// --------------------------------------------------------------------------
	// ! SQL Clause Arrays
	// --------------------------------------------------------------------------

	/**
	 * Keys for insert/update statement
	 * @var array
	 */
	protected $set_array_keys = array();

	/**
	 * Key/val pairs for order by clause
	 * @var array
	 */
	protected $order_array = array();

	/**
	 * Key/val pairs for group by clause
	 * @var array
	 */
	protected $group_array = array();

	// --------------------------------------------------------------------------
	// ! Other Class vars
	// --------------------------------------------------------------------------

	/**
	 * Values to apply to prepared statements
	 * @var array
	 */
	protected $values = array();

	/**
	 * Values to apply to where clauses in prepared statements
	 * @var array
	 */
	protected $where_values = array();

	/**
	 * Value for limit string
	 * @var string
	 */
	protected $limit;

	/**
	 * Value for offset in limit string
	 * @var int
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
	protected $query_map = array();

	/**
	 * Map for having clause
	 * @var array
	 */
	protected $having_map;

	/**
	 * Convenience property for connection management
	 * @var string
	 */
	public $conn_name = "";

	/**
	 * List of queries executed
	 * @var array
	 */
	public $queries;

	/**
	 * Whether to do only an explain on the query
	 * @var bool
	 */
	protected $explain;

	/**
	 * The current database driver
	 * @var Driver_Interface
	 */
	public $db;

	/**
	 * Query parser class instance
	 * @var Query_Parser
	 */
	protected $parser;

	/**
	 * Alias to driver util class
	 * @var \Query\Driver\Abstract_Util
	 */
	public $util;

	/**
	 * Alias to driver sql class
	 * @var \Query\Driver\SQL_Interface
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
	 * @param int $val_type
	 * @return array
	 */
	protected function _mixed_set(&$var, $key, $val=NULL, $val_type=self::BOTH)
	{
		$arg = (is_scalar($key) && is_scalar($val))
			? array($key => $val)
			: $key;

		foreach($arg as $k => $v)
		{
			if (in_array($val_type, array(self::KEY, self::VALUE)))
			{
				$var[] = ($val_type === self::KEY)
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

	// --------------------------------------------------------------------------

	/**
	 * Method to simplify select_ methods
	 *
	 * @param string $field
	 * @param string|bool $as
	 * @return string
	 */
	protected function _select($field, $as = FALSE)
	{
		// Escape the identifiers
		$field = $this->db->quote_ident($field);

		$as = ($as !== FALSE)
			? $this->db->quote_ident($as)
			: $field;

		return "({$field}) AS {$as} ";
	}

	// --------------------------------------------------------------------------

	/**
	 * Helper function for returning sql strings
	 *
	 * @param string $type
	 * @param string $table
	 * @param bool $reset
	 * @return string
	 */
	protected function _get_compile($type, $table, $reset)
	{
		$sql = $this->_compile($type, $table);

		// Reset the query builder for the next query
		if ($reset) $this->reset_query();

		return $sql;
	}

	// --------------------------------------------------------------------------

	/**
	 * Simplify 'like' methods
	 *
	 * @param string $field
	 * @param mixed $val
	 * @param string $pos
	 * @param string $like
	 * @param string $conj
	 * @return Query_Builder
	 */
	protected function _like($field, $val, $pos, $like='LIKE', $conj='AND')
	{
		$field = $this->db->quote_ident($field);

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

		$conj = (empty($this->query_map)) ? ' WHERE ' : " {$conj} ";
		$this->_append_map($conj, $like, 'like');

		// Add to the values array
		$this->where_values[] = $val;

		return $this;
	}

	// --------------------------------------------------------------------------

	/**
	 * Simplify building having clauses
	 *
	 * @param mixed $key
	 * @param mixed $val
	 * @param string $conj
	 * @return Query_Builder
	 */
	protected function _having($key, $val=array(), $conj='AND')
	{
		$where = $this->_where($key, $val);

		// Create key/value placeholders
		foreach($where as $f => $val)
		{
			// Split each key by spaces, in case there
			// is an operator such as >, <, !=, etc.
			$f_array = explode(' ', trim($f));

			$item = $this->db->quote_ident($f_array[0]);

			// Simple key value, or an operator
			$item .= (count($f_array) === 1) ? '=?' : " {$f_array[1]} ?";

			// Put in the having map
			$this->having_map[] = array(
				'conjunction' => ( ! empty($this->having_map)) ? " {$conj} " : ' HAVING ',
				'string' => $item
			);
		}

		return $this;
	}

	// --------------------------------------------------------------------------

	/**
	 * Do all the repeditive stuff for where/having type methods
	 *
	 * @param mixed $key
	 * @param mixed $val
	 * @return array
	 */
	protected function _where($key, $val=array())
	{
		$where = array();
		$this->_mixed_set($where, $key, $val, self::BOTH);
		$this->_mixed_set($this->where_values, $key, $val, self::VALUE);
		return $where;
	}

	// --------------------------------------------------------------------------

	/**
	 * Simplify generating where string
	 *
	 * @param mixed $key
	 * @param mixed $val
	 * @param string $defaultConj
	 * @return Query_Builder
	 */
	protected function _where_string($key, $val=array(), $defaultConj='AND')
	{
		// Create key/value placeholders
		foreach($this->_where($key, $val) as $f => $val)
		{
			// Split each key by spaces, in case there
			// is an operator such as >, <, !=, etc.
			$f_array = explode(' ', trim($f));

			$item = $this->db->quote_ident($f_array[0]);

			// Simple key value, or an operator
			$item .= (count($f_array) === 1) ? '=?' : " {$f_array[1]} ?";
			$last_item = end($this->query_map);

			// Determine the correct conjunction
			$conjunctionList = array_pluck($this->query_map, 'conjunction');
			if (empty($this->query_map) || ( ! regex_in_array($conjunctionList, "/^ ?\n?WHERE/i")))
			{
				$conj = "\nWHERE ";
			}
			elseif ($last_item['type'] === 'group_start')
			{
				$conj = '';
			}
			else
			{
				$conj = " {$defaultConj} ";
			}

			$this->_append_map($conj, $item, 'where');
		}

		return $this;
	}

	// --------------------------------------------------------------------------

	/**
	 * Simplify where_in methods
	 *
	 * @param mixed $key
	 * @param mixed $val
	 * @param string $in - The (not) in fragment
	 * @param string $conj - The where in conjunction
	 * @return Query_Builder
	 */
	protected function _where_in($key, $val=array(), $in='IN', $conj='AND')
	{
		$key = $this->db->quote_ident($key);
		$params = array_fill(0, count($val), '?');

		foreach($val as $v)
		{
			$this->where_values[] = $v;
		}

		$conjunction = ( ! empty($this->query_map)) ? " {$conj} " : ' WHERE ';
		$str = $key . " {$in} (".implode(',', $params).') ';

		$this->_append_map($conjunction, $str, 'where_in');

		return $this;
	}

	// --------------------------------------------------------------------------

	/**
	 * Executes the compiled query
	 *
	 * @param string $type
	 * @param string $table
	 * @param string $sql
	 * @param array|null $vals
	 * @return \PDOStatement
	 */
	protected function _run($type, $table, $sql=NULL, $vals=NULL)
	{
		if (is_null($sql))
		{
			$sql = $this->_compile($type, $table);
		}

		if (is_null($vals))
		{
			$vals = array_merge($this->values, (array) $this->where_values);
		}

		$start_time = microtime(TRUE);

		$res = (empty($vals))
			? $this->db->query($sql)
			: $this->db->prepare_execute($sql, $vals);

		$end_time = microtime(TRUE);
		$total_time = number_format($end_time - $start_time, 5);

		// Add this query to the list of executed queries
		$this->_append_query($vals, $sql, $total_time);

		// Reset class state for next query
		$this->reset_query();

		return $res;
	}

	// --------------------------------------------------------------------------

	/**
	 * Add an additional set of mapping pairs to a internal map
	 *
	 * @param string $conjunction
	 * @param string $string
	 * @param string $type
	 * @return void
	 */
	protected function _append_map($conjunction = '', $string = '', $type = '')
	{
		array_push($this->query_map, array(
			'type' => $type,
			'conjunction' => $conjunction,
			'string' => $string
		));
	}

	// --------------------------------------------------------------------------

	/**
	 * Convert the prepared statement into readable sql
	 *
	 * @param array $vals
	 * @param string $sql
	 * @param string $total_time
	 * @return void
	 */
	protected function _append_query($vals, $sql, $total_time)
	{
		$evals = (is_array($vals)) ? $vals : array();
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
		$this->queries[] = array(
			'time' => $total_time,
			'sql' => call_user_func_array('sprintf', $evals),
		);

		$this->queries['total_time'] += $total_time;

		// Set the last query to get rowcounts properly
		$this->db->last_query = $sql;
	}

	// --------------------------------------------------------------------------

	/**
	 * Sub-method for generating sql strings
	 *
	 * @param string $type
	 * @param string $table
	 * @return string
	 */
	protected function _compile_type($type='', $table='')
	{
		if ($type === 'insert')
		{
			$param_count = count($this->set_array_keys);
			$params = array_fill(0, $param_count, '?');
			$sql = "INSERT INTO {$table} ("
				. implode(',', $this->set_array_keys)
				. ")\nVALUES (".implode(',', $params).')';
		}
		elseif ($type === 'update')
		{
			$sql = "UPDATE {$table}\nSET {$this->set_string}";
		}
		elseif ($type === 'delete')
		{
			$sql = "DELETE FROM {$table}";
		}
		else // GET queries
		{
			$sql = "SELECT * \nFROM {$this->from_string}";

			// Set the select string
			if ( ! empty($this->select_string))
			{
				// Replace the star with the selected fields
				$sql = str_replace('*', $this->select_string, $sql);
			}
		}

		return $sql;
	}

	// --------------------------------------------------------------------------

	/**
	 * String together the sql statements for sending to the db
	 *
	 * @param string $type
	 * @param string $table
	 * @return string
	 */
	protected function _compile($type='', $table='')
	{
		// Get the base clause for the query
		$sql = $this->_compile_type($type, $this->db->quote_table($table));

		$clauses = array(
			'query_map',
			'group_string',
			'order_string',
			'having_map',
		);

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

// End of abstract_query_builder.php