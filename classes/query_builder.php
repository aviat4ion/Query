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

/**
 * Convienience class for creating sql queries - also the class that
 * instantiates the specific db driver
 *
 * @package Query
 * @subpackage Query_Builder
 */
class Query_Builder implements Query_Builder_Interface {

	// --------------------------------------------------------------------------
	// ! SQL Clause Strings
	// --------------------------------------------------------------------------

	/**
	 * Compiled 'select' clause
	 * @var type string
	 */
	protected $select_string = '';

	/**
	 * Compiled 'from' clause
	 * @var type string
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
	 * @var type string
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
	 * Alias to $this->db->util
	 * @var DB_Util
	 */
	public $util;

	/**
	 * Alias to $this->db->sql
	 * @var SQL_Interface
	 */
	public $sql;

	// --------------------------------------------------------------------------
	// ! Methods
	// --------------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @param Abstract_driver $db
	 * @param ArrayObject $params - the connection parameters
	 */
	public function __construct(Driver_Interface $db, ArrayObject $params)
	{
		$this->db = $db;

		// Instantiate the Query Parser
		$this->parser = new Query_Parser();

		$this->queries['total_time'] = 0;

		// Make things just slightly shorter
		$this->sql = $this->db->sql;
		$this->util = $this->db->util;
	}

	// --------------------------------------------------------------------------

	/**
	 * Destructor
	 */
	public function __destruct()
	{
		$this->db = NULL;
	}

	// --------------------------------------------------------------------------
	// ! Select Queries
	// --------------------------------------------------------------------------

	/**
	 * Method to simplify select_ methods
	 *
	 * @param string $field
	 * @param string $as
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

	/**
	 * Specifies rows to select in a query
	 *
	 * @param string $fields
	 * @return Query_Builder
	 */
	public function select($fields)
	{
		// Split fields by comma
		$fields_array = explode(",", $fields);
		$fields_array = array_map('mb_trim', $fields_array);

		// Split on 'As'
		foreach ($fields_array as $key => $field)
		{
			if (stripos($field, 'as') !== FALSE)
			{
				$fields_array[$key] = preg_split('` as `i', $field);
				$fields_array[$key] = array_map('mb_trim', $fields_array[$key]);
			}
		}

		// Quote the identifiers
		$safe_array = $this->db->quote_ident($fields_array);

		unset($fields_array);

		// Join the strings back together
		for($i = 0, $c = count($safe_array); $i < $c; $i++)
		{
			if (is_array($safe_array[$i]))
			{
				$safe_array[$i] = implode(' AS ', $safe_array[$i]);
			}
		}

		$this->select_string .= implode(', ', $safe_array);

		unset($safe_array);

		return $this;
	}

	// --------------------------------------------------------------------------

	/**
	 * Selects the maximum value of a field from a query
	 *
	 * @param string $field
	 * @param string $as
	 * @return Query_Builder
	 */
	public function select_max($field, $as=FALSE)
	{
		// Create the select string
		$this->select_string .= ' MAX'.$this->_select($field, $as);
		return $this;
	}

	// --------------------------------------------------------------------------

	/**
	 * Selects the minimum value of a field from a query
	 *
	 * @param string $field
	 * @param string $as
	 * @return Query_Builder
	 */
	public function select_min($field, $as=FALSE)
	{
		// Create the select string
		$this->select_string .= ' MIN'.$this->_select($field, $as);
		return $this;
	}

	// --------------------------------------------------------------------------

	/**
	 * Selects the average value of a field from a query
	 *
	 * @param string $field
	 * @param string $as
	 * @return Query_Builder
	 */
	public function select_avg($field, $as=FALSE)
	{
		// Create the select string
		$this->select_string .= ' AVG'.$this->_select($field, $as);
		return $this;
	}

	// --------------------------------------------------------------------------

	/**
	 * Selects the sum of a field from a query
	 *
	 * @param string $field
	 * @param string $as
	 * @return Query_Builder
	 */
	public function select_sum($field, $as=FALSE)
	{
		// Create the select string
		$this->select_string .= ' SUM'.$this->_select($field, $as);
		return $this;
	}

	// --------------------------------------------------------------------------

	/**
	 * Adds the 'distinct' keyword to a query
	 *
	 * @return Query_Builder
	 */
	public function distinct()
	{
		// Prepend the keyword to the select string
		$this->select_string = ' DISTINCT '.$this->select_string;
		return $this;
	}

	// --------------------------------------------------------------------------

	/**
	 * Tell the database to give you the query plan instead of result set
	 *
	 * @return Query_Builder
	 */
	public function explain()
	{
		$this->explain = TRUE;
		return $this;
	}

	// --------------------------------------------------------------------------

	/**
	 * Specify the database table to select from
	 *
	 * @param string $tblname
	 * @return Query_Builder
	 */
	public function from($tblname)
	{
		// Split identifiers on spaces
		$ident_array = explode(' ', mb_trim($tblname));
		$ident_array = array_map('mb_trim', $ident_array);

		// Quote the identifiers
		$ident_array[0] = $this->db->quote_table($ident_array[0]);
		$ident_array = $this->db->quote_ident($ident_array);

		// Paste it back together
		$this->from_string = implode(' ', $ident_array);

		return $this;
	}

	// --------------------------------------------------------------------------
	// ! 'Like' methods
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
		$l = $field. " {$like} ?";

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
		$this->_append_map($conj, $l, 'like');

		// Add to the values array
		$this->where_values[] = $val;

		return $this;
	}

	// --------------------------------------------------------------------------

	/**
	 * Creates a Like clause in the sql statement
	 *
	 * @param string $field
	 * @param mixed $val
	 * @param string $pos
	 * @return Query_Builder
	 */
	public function like($field, $val, $pos='both')
	{
		return $this->_like($field, $val, $pos, 'LIKE', 'AND');
	}

	// --------------------------------------------------------------------------

	/**
	 * Generates an OR Like clause
	 *
	 * @param string $field
	 * @param mixed $val
	 * @param string $pos
	 * @return Query_Builder
	 */
	public function or_like($field, $val, $pos='both')
	{
		return $this->_like($field, $val, $pos, 'LIKE', 'OR');
	}

	// --------------------------------------------------------------------------

	/**
	 * Generates a NOT LIKE clause
	 *
	 * @param string $field
	 * @param mixed $val
	 * @param string $pos
	 * @return Query_Builder
	 */
	public function not_like($field, $val, $pos='both')
	{
		return $this->_like($field, $val, $pos, 'NOT LIKE', 'AND');
	}

	// --------------------------------------------------------------------------

	/**
	 * Generates a OR NOT LIKE clause
	 *
	 * @param string $field
	 * @param mixed $val
	 * @param string $pos
	 * @return Query_Builder
	 */
	public function or_not_like($field, $val, $pos='both')
	{
		return $this->_like($field, $val, $pos, 'NOT LIKE', 'OR');
	}

	// --------------------------------------------------------------------------
	// ! Having methods
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
	 * Generates a 'Having' clause
	 *
	 * @param mixed $key
	 * @param mixed $val
	 * @return Query_Builder
	 */
	public function having($key, $val=array())
	{
		return $this->_having($key, $val, 'AND');
	}

	// --------------------------------------------------------------------------

	/**
	 * Generates a 'Having' clause prefixed with 'OR'
	 *
	 * @param mixed $key
	 * @param mixed $val
	 * @return Query_Builder
	 */
	public function or_having($key, $val=array())
	{
		return $this->_having($key, $val, 'OR');
	}

	// --------------------------------------------------------------------------
	// ! 'Where' methods
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

		// Key and value passed? Add them to the where array
		if (is_scalar($key) && is_scalar($val))
		{
			$where[$key] = $val;
			$this->where_values[] = $val;
		}
		// Array or object, loop through and add to the where array
		elseif ( ! is_scalar($key))
		{
			foreach($key as $k => $v)
			{
				$where[$k] = $v;
				$this->where_values[] = $v;
			}
		}

		return $where;
	}

	// --------------------------------------------------------------------------

	/**
	 * Simplify generating where string
	 *
	 * @param mixed $key
	 * @param mixed $val
	 * @param string $conj
	 * @return Query_Builder
	 */
	protected function _where_string($key, $val=array(), $conj='AND')
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

			// Get the type of the first item in the query map
			$first_item = end($this->query_map);

			// Determine the correct conjunction
			if (empty($this->query_map))
			{
				$conj = "\nWHERE ";
			}
			elseif ($first_item['type'] === 'group_start')
			{
				$conj = '';
			}
			else
			{
				$conj = " {$conj} ";
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
	 * Specify condition(s) in the where clause of a query
	 * Note: this function works with key / value, or a
	 * passed array with key / value pairs
	 *
	 * @param mixed $key
	 * @param mixed $val
	 * @param mixed $escape
	 * @return Query_Builder
	 */
	public function where($key, $val=array(), $escape=NULL)
	{
		return $this->_where_string($key, $val, 'AND');
	}

	// --------------------------------------------------------------------------

	/**
	 * Where clause prefixed with "OR"
	 *
	 * @param string $key
	 * @param mixed $val
	 * @return Query_Builder
	 */
	public function or_where($key, $val=array())
	{
		return $this->_where_string($key, $val, 'OR');
	}

	// --------------------------------------------------------------------------

	/**
	 * Where clause with 'IN' statement
	 *
	 * @param mixed $field
	 * @param mixed $val
	 * @return Query_Builder
	 */
	public function where_in($field, $val=array())
	{
		return $this->_where_in($field, $val);
	}

	// --------------------------------------------------------------------------

	/**
	 * Where in statement prefixed with "or"
	 *
	 * @param string $field
	 * @param mixed $val
	 * @return Query_Builder
	 */
	public function or_where_in($field, $val=array())
	{
		return $this->_where_in($field, $val, 'IN', 'OR');
	}

	// --------------------------------------------------------------------------

	/**
	 * WHERE NOT IN (FOO) clause
	 *
	 * @param string $field
	 * @param mixed $val
	 * @return Query_Builder
	 */
	public function where_not_in($field, $val=array())
	{
		return $this->_where_in($field, $val, 'NOT IN', 'AND');
	}

	// --------------------------------------------------------------------------

	/**
	 * OR WHERE NOT IN (FOO) clause
	 *
	 * @param string $field
	 * @param mixed $val
	 * @return Query_Builder
	 */
	public function or_where_not_in($field, $val=array())
	{
		return $this->_where_in($field, $val, 'NOT IN', 'OR');
	}

	// --------------------------------------------------------------------------
	// ! Other Query Modifier methods
	// --------------------------------------------------------------------------

	/**
	 * Sets values for inserts / updates / deletes
	 *
	 * @param mixed $key
	 * @param mixed $val
	 * @return Query_Builder
	 */
	public function set($key, $val = NULL)
	{
		// Plain key, value pair
		if (is_scalar($key) && is_scalar($val))
		{
			$this->set_array_keys[] = $key;
			$this->values[] = $val;
		}
		// Object or array
		elseif (is_array($key) || is_object($key))
		{
			foreach($key as $k => $v)
			{
				$this->set_array_keys[] = $k;
				$this->values[] = $v;
			}
		}

		// Use the keys of the array to make the insert/update string
		// Escape the field names
		$this->set_array_keys = array_map(array($this->db, '_quote'), $this->set_array_keys);

		// Generate the "set" string
		$this->set_string = implode('=?,', $this->set_array_keys);
		$this->set_string .= '=?';

		return $this;
	}

	// --------------------------------------------------------------------------

	/**
	 * Creates a join phrase in a compiled query
	 *
	 * @param string $table
	 * @param string $condition
	 * @param string $type
	 * @return Query_Builder
	 */
	public function join($table, $condition, $type='')
	{
		// Prefix and quote table name
		$table = explode(' ', mb_trim($table));
		$table[0] = $this->db->quote_table($table[0]);
		$table = $this->db->quote_ident($table);
		$table = implode(' ', $table);

		// Parse out the join condition
		$parts = $this->parser->parse_join($condition);
		$count = count($parts['identifiers']);

		// Go through and quote the identifiers
		for($i=0; $i <= $count; $i++)
		{
			if (in_array($parts['combined'][$i], $parts['identifiers']) && ! is_numeric($parts['combined'][$i]))
			{
				$parts['combined'][$i] = $this->db->quote_ident($parts['combined'][$i]);
			}
		}

		$parsed_condition = implode('', $parts['combined']);
		$condition = $table . ' ON ' . $parsed_condition;

		$this->_append_map("\n" . strtoupper($type) . ' JOIN ', $condition, 'join');

		return $this;
	}

	// --------------------------------------------------------------------------

	/**
	 * Group the results by the selected field(s)
	 *
	 * @param mixed $field
	 * @return Query_Builder
	 */
	public function group_by($field)
	{
		if ( ! is_scalar($field))
		{
			$new_group_array = array_map(array($this->db, 'quote_ident'), $field);
			$this->group_array = array_merge($this->group_array, $new_group_array);
		}
		else
		{
			$this->group_array[] = $this->db->quote_ident($field);
		}

		$this->group_string = ' GROUP BY ' . implode(',', $this->group_array);

		return $this;
	}

	// --------------------------------------------------------------------------

	/**
	 * Order the results by the selected field(s)
	 *
	 * @param string $field
	 * @param string $type
	 * @return Query_Builder
	 */
	public function order_by($field, $type="")
	{
		// Random case
		if (stripos($type, 'rand') !== FALSE)
		{
			$type = (($rand = $this->sql->random()) !== FALSE ) ? $rand : 'ASC';
		}

		// Set fields for later manipulation
		$field = $this->db->quote_ident($field);
		$this->order_array[$field] = $type;

		$order_clauses = array();

		// Flatten key/val pairs into an array of space-separated pairs
		foreach($this->order_array as $k => $v)
		{
			$order_clauses[] = $k . ' ' . strtoupper($v);
		}

		// Set the final string
		$this->order_string = (empty($rand))
			? "\nORDER BY ".implode(', ', $order_clauses)
			: "\nORDER BY".$rand;

		return $this;
	}

	// --------------------------------------------------------------------------

	/**
	 * Set a limit on the current sql statement
	 *
	 * @param int $limit
	 * @param int $offset
	 * @return Query_Builder
	 */
	public function limit($limit, $offset=FALSE)
	{
		$this->limit = $limit;
		$this->offset = $offset;

		return $this;
	}

	// --------------------------------------------------------------------------
	// ! Query Grouping Methods
	// --------------------------------------------------------------------------

	/**
	 * Adds a paren to the current query for query grouping
	 *
	 * @return Query_Builder
	 */
	public function group_start()
	{
		$conj = (empty($this->query_map)) ? ' WHERE ' : ' ';

		$this->_append_map($conj, '(', 'group_start');

		return $this;
	}

	// --------------------------------------------------------------------------

	/**
	 * Adds a paren to the current query for query grouping,
	 * prefixed with 'OR'
	 *
	 * @return Query_Builder
	 */
	public function or_group_start()
	{
		$this->_append_map('', ' OR (', 'group_start');

		return $this;
	}

	// --------------------------------------------------------------------------

	/**
	 * Adds a paren to the current query for query grouping,
	 * prefixed with 'OR NOT'
	 *
	 * @return Query_Builder
	 */
	public function or_not_group_start()
	{
		$this->_append_map('', ' OR NOT (', 'group_start');

		return $this;
	}

	// --------------------------------------------------------------------------

	/**
	 * Ends a query group
	 *
	 * @return Query_Builder
	 */
	public function group_end()
	{
		$this->_append_map('', ')', 'group_end');

		return $this;
	}

	// --------------------------------------------------------------------------
	// ! Query execution methods
	// --------------------------------------------------------------------------

	/**
	 * Select and retrieve all records from the current table, and/or
	 * execute current compiled query
	 *
	 * @param $table
	 * @param int $limit
	 * @param int $offset
	 * @return PDOStatement
	 */
	public function get($table='', $limit=FALSE, $offset=FALSE)
	{
		// Set the table
		if ( ! empty($table))
		{
			$this->from($table);
		}

		// Set the limit, if it exists
		if ($limit !== FALSE)
		{
			$this->limit($limit, $offset);
		}

		return $this->_run("get", $table);
	}

	// --------------------------------------------------------------------------

	/**
	 * Convience method for get() with a where clause
	 *
	 * @param string $table
	 * @param array $where
	 * @param int $limit
	 * @param int $offset
	 * @return PDOStatement
	 */
	public function get_where($table, $where=array(), $limit=FALSE, $offset=FALSE)
	{
		// Create the where clause
		$this->where($where);

		// Return the result
		return $this->get($table, $limit, $offset);
	}

	// --------------------------------------------------------------------------

	/**
	 * Retreive the number of rows in the selected table
	 *
	 * @param string $table
	 * @return int
	 */
	public function count_all($table)
	{
		$sql = 'SELECT * FROM '.$this->db->quote_table($table);
		$res = $this->db->query($sql);
		return (int) count($res->fetchAll());
	}

	// --------------------------------------------------------------------------

	/**
	 * Retrieve the number of results for the generated query - used
	 * in place of the get() method
	 *
	 * @param string $table
	 * @return int
	 */
	public function count_all_results($table='')
	{
		// Set the table
		if ( ! empty($table))
		{
			$this->from($table);
		}

		$result = $this->_run('get', $table);


		$rows = $result->fetchAll();

		return (int) count($rows);
	}

	// --------------------------------------------------------------------------

	/**
	 * Creates an insert clause, and executes it
	 *
	 * @param string $table
	 * @param mixed $data
	 * @return PDOStatement
	 */
	public function insert($table, $data=array())
	{
		// No use duplicating logic!
		if ( ! empty($data))
		{
			$this->set($data);
		}

		return $this->_run("insert", $table);
	}

	// --------------------------------------------------------------------------

	/**
	 * Creates and executes a batch insertion query
	 *
	 * @param string $table
	 * @param array $data
	 * @return PDOStatement
	 */
	public function insert_batch($table, $data=array())
	{
		// Get the generated values and sql string
		list($sql, $data) = $this->db->insert_batch($table, $data);

		if ( ! is_null($sql))
		{
			return $this->_run('', $table, $sql, $data);
		}

		return NULL;
	}

	// --------------------------------------------------------------------------

	/**
	 * Creates an update clause, and executes it
	 *
	 * @param string $table
	 * @param mixed $data
	 * @return PDOStatement
	 */
	public function update($table, $data=array())
	{
		// No use duplicating logic!
		if ( ! empty($data))
		{
			$this->set($data);
		}

		return $this->_run("update", $table);
	}

	// --------------------------------------------------------------------------

	/**
	 * Deletes data from a table
	 *
	 * @param string $table
	 * @param mixed $where
	 * @return PDOStatement
	 */
	public function delete($table, $where='')
	{
		// Set the where clause
		if ( ! empty($where))
		{
			$this->where($where);
		}

		return $this->_run("delete", $table);
	}

	// --------------------------------------------------------------------------
	// ! SQL Returning Methods
	// --------------------------------------------------------------------------

	/**
	 * Helper function for returning sql strings
	 *
	 * @param string $type
	 * @param string $table
	 * @param bool $reset
	 * @resturn string
	 */
	protected function _get_compile($type, $table, $reset)
	{
		$sql = $this->_compile($type, $table);

		// Reset the query builder for the next query
		if ($reset)
		{
			$this->reset_query();
		}

		return $sql;
	}

	// --------------------------------------------------------------------------

	/**
	 * Returns the generated 'select' sql query
	 *
	 * @param string $table
	 * @param bool $reset
	 * @return string
	 */
	public function get_compiled_select($table='', $reset=TRUE)
	{
		// Set the table
		if ( ! empty($table))
		{
			$this->from($table);
		}

		return $this->_get_compile('select', $table, $reset);
	}

	// --------------------------------------------------------------------------

	/**
	 * Returns the generated 'insert' sql query
	 *
	 * @param string $table
	 * @param bool $reset
	 * @return string
	 */
	public function get_compiled_insert($table, $reset=TRUE)
	{
		return $this->_get_compile('insert', $table, $reset);
	}

	// --------------------------------------------------------------------------

	/**
	 * Returns the generated 'update' sql query
	 *
	 * @param string $table
	 * @param bool $reset
	 * @return string
	 */
	public function get_compiled_update($table='', $reset=TRUE)
	{
		return $this->_get_compile('update', $table, $reset);
	}

	// --------------------------------------------------------------------------

	/**
	 * Returns the generated 'delete' sql query
	 *
	 * @param string $table
	 * @param bool $reset
	 * @return string
	 */
	public function get_compiled_delete($table="", $reset=TRUE)
	{
		return $this->_get_compile('delete', $table, $reset);
	}


	// --------------------------------------------------------------------------
	// ! Miscellaneous Methods
	// --------------------------------------------------------------------------

	/**
	 * Clear out the class variables, so the next query can be run
	 *
	 * @return void
	 */
	public function reset_query()
	{
		$null_properties = array(
			'select_string',
			'from_string',
			'set_string',
			'order_string',
			'group_string',
			'limit',
			'offset',
			'explain'
		);

		$array_properties = array(
			'set_array_keys',
			'order_array',
			'group_array',
			'values',
			'where_values',
			'query_map',
			'having_map'
		);

		// Reset strings and booleans
		foreach($null_properties as $var)
		{
			$this->$var = NULL;
		}

		// Reset arrays
		foreach($array_properties as $var)
		{
			$this->$var = array();
		}
	}

	// --------------------------------------------------------------------------

	/**
	 * Executes the compiled query
	 *
	 * @param string $type
	 * @param string $table
	 * @param string $sql
	 * @param array|null $vals
	 * @return PDOStatement
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

		if (empty($vals))
		{
			$res = $this->db->query($sql);
		}
		else
		{
			$res = $this->db->prepare_execute($sql, $vals);
		}

		$end_time = microtime(TRUE);
		$total_time = number_format($end_time - $start_time, 5);


		$this->_append_query($vals, $sql, $total_time);

		// Reset class state for next query
		$this->reset_query();

		return $res;
	}

	// --------------------------------------------------------------------------

	/**
	 * Calls a function further down the inheritence chain
	 *
	 * @param string $name
	 * @param array $params
	 * @return mixed
	 * @throws BadMethodCallException
	 */
	public function __call($name, $params)
	{
		if (method_exists($this->db, $name))
		{
			return call_user_func_array(array($this->db, $name), $params);
		}

		throw new BadMethodCallException("Method does not exist");
	}

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

		// Quote string values
		foreach($evals as &$v)
		{
			$v = ( ! is_numeric($v)) ? htmlentities($this->db->quote($v), ENT_NOQUOTES, 'utf-8', FALSE)  : $v;
		}
		$esql = str_replace('?', "%s", $sql);

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
	 * @return $string
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
// End of query_builder.php