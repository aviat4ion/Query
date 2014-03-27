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
 * @subpackage Query
 */
class Query_Builder extends Abstract_Query_Builder {

	// --------------------------------------------------------------------------
	// ! SQL Clause Strings
	// --------------------------------------------------------------------------

	// Compiled 'select' clause
	protected $select_string = '';

	// Compiled 'from' clause
	protected $from_string;

	// Compiled arguments for insert / update
	protected $set_string;

	// Order by clause
	protected $order_string;

	// Group by clause
	protected $group_string;

	// --------------------------------------------------------------------------
	// ! SQL Clause Arrays
	// --------------------------------------------------------------------------

	// Keys for insert/update statement
	protected $set_array_keys = array();

	// Key/val pairs for order by clause
	protected $order_array = array();

	// Key/val pairs for group by clause
	protected $group_array = array();

	// --------------------------------------------------------------------------
	// ! Other Class vars
	// --------------------------------------------------------------------------

	// Values to apply to prepared statements
	protected $values = array();

	// Values to apply to where clauses in prepared statements
	protected $where_values = array();

	// Value for limit string
	protected $limit;

	// Value for offset in limit string
	protected $offset;

	// Alias to $this->db->sql
	public $sql;

	// Query component order mapping
	// for complex select queries
	//
	// Format:
	// array(
	// 		'type' => 'where',
	//		'conjunction' => ' AND ',
	// 		'string' => 'k=?'
	// )
	protected $query_map = array();

	// Map for having clause
	protected $having_map;

	// Convenience property for connection management
	public $conn_name = "";

	// List of sql queries executed
	public $queries;

	// Whether to do only an explain on the query
	protected $explain;

	// Subclass instances
	public $db;
	protected $parser;
	public $util;

	// --------------------------------------------------------------------------
	// ! Methods
	// --------------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @param DB_PDO $db
	 * @param object $params - the connection parameters
	 */
	public function __construct($db, $params)
	{
		$this->db = $db;

		// Set the connection name property, if applicable
		if (isset($params->name))
		{
			$this->conn_name = $params->name;
		}

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
	 * Specifies rows to select in a query
	 *
	 * @param string $fields
	 * @return $this
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
	 * @return $this
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
	 * @return $this
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
	 * @return $this
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
	 * @return $this
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
	 * @return $this
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
	 * @return $this
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
	 * @return $this
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
	 * Creates a Like clause in the sql statement
	 *
	 * @param string $field
	 * @param mixed $val
	 * @param string $pos
	 * @return $this
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
	 * @return $this
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
	 * @return $this
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
	 * @return $this
	 */
	public function or_not_like($field, $val, $pos='both')
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
	 * @return $this
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
	 * @return $this
	 */
	public function or_having($key, $val=array())
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
	 * @return $this
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
	 * @return $this
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
	 * @return $this
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
	 * @return $this
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
	 * @return $this
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
	 * @return $this
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
	 * @return $this
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
	 * @return $this
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

		$this->query_map[] = array(
			'type' => 'join',
			'conjunction' => "\n" . strtoupper($type) . ' JOIN ',
			'string' => $condition,
		);

		return $this;
	}

	// --------------------------------------------------------------------------

	/**
	 * Group the results by the selected field(s)
	 *
	 * @param mixed $field
	 * @return $this
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
	 * @return $this
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
	 * @return $this
	 */
	public function group_start()
	{
		$this->query_map[] = array(
			'type' => 'group_start',
			'conjunction' => (empty($this->query_map)) ? ' WHERE ' : ' ',
			'string' => '('
		);

		return $this;
	}

	// --------------------------------------------------------------------------

	/**
	 * Adds a paren to the current query for query grouping,
	 * prefixed with 'OR'
	 *
	 * @return $this
	 */
	public function or_group_start()
	{
		$this->query_map[] = array(
			'type' => 'group_start',
			'conjunction' => '',
			'string' => ' OR ('
		);

		return $this;
	}

	// --------------------------------------------------------------------------

	/**
	 * Adds a paren to the current query for query grouping,
	 * prefixed with 'OR NOT'
	 *
	 * @return $this
	 */
	public function or_not_group_start()
	{
		$this->query_map[] = array(
			'type' => 'group_start',
			'conjunction' => '',
			'string' => ' OR NOT ('
		);

		return $this;
	}

	// --------------------------------------------------------------------------

	/**
	 * Ends a query group
	 *
	 * @return $this
	 */
	public function group_end()
	{
		$this->query_map[] = array(
			'type' => 'group_end',
			'conjunction' => '',
			'string' => ')'
		);

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
	 * @return object
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
	 * @return object
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
	 * @return mixed
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
	 * Create sql for batch insert
	 *
	 * @param string $table
	 * @param array $data
	 * @return string
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
	 * @return mixed
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
	 * @return mixed
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
}
// End of query_builder.php