<?php
/**
 * Query
 *
 * Free Query Builder / Database Abstraction Layer
 *
 * @package		Query
 * @author		Timothy J. Warren
 * @copyright	Copyright (c) 2012
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
class Query_Builder {

	// --------------------------------------------------------------------------
	// ! SQL Clause Strings
	// --------------------------------------------------------------------------

	// Compiled 'select' clause
	private $select_string;

	// Compiled 'from' clause
	private $from_string;

	// Compiled arguments for insert / update
	private $set_string;

	// Order by clause
	private $order_string;

	// Group by clause
	private $group_string;

	// --------------------------------------------------------------------------
	// ! SQL Clause Arrays
	// --------------------------------------------------------------------------

	// Keys for insert/update statement
	private $set_array_keys;

	// Key/val pairs for order by clause
	private $order_array;

	// Key/val pairs for group by clause
	private $group_array;

	// --------------------------------------------------------------------------
	// ! Other Class vars
	// --------------------------------------------------------------------------

	// Values to apply to prepared statements
	private $values = array();

	// Values to apply to where clauses in prepared statements
	private $where_values = array();

	// Value for limit string
	private $limit;

	// Value for offset in limit string
	private $offset;

	// Alias to $this->db->sql
	public $sql;

	// Database table prefix
	public $table_prefix = '';

	// Query component order mapping
	// for complex select queries
	//
	// Format:
	// array(
	// 		'type' => 'where',
	//		'conjunction' => ' AND ',
	// 		'string' => 'k=?'
	// )
	private $query_map;

	// Map for having clause
	private $having_map;

	// Convenience property for connection management
	public $conn_name = "";

	// List of sql queries executed
	public $queries;

	// --------------------------------------------------------------------------
	// ! Methods
	// --------------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @param object $params - the connection parametere
	 */
	public function __construct($params)
	{
		// Convert array to object
		if (is_array($params))
		{
			$params = new ArrayObject($params, ArrayObject::STD_PROP_LIST | ArrayObject::ARRAY_AS_PROPS);
		}
		
		$params->type = strtolower($params->type);
		$dbtype = ($params->type !== 'postgresql') ? $params->type : 'pgsql';

		// Generate dsn
		$dsn = $this->_connect($dbtype, $params);

		try
		{
			// Create the database connection
			$this->db = ( ! empty($params->user))
				? new $dbtype($dsn, $params->user, $params->pass)
				: new $dbtype($dsn);
		}
		catch(Exception $e)
		{
			throw new BadConnectionException('Connection failed, invalid arguments', 2);
		}
		
		// Set the table prefix, if it exists
		if (isset($params->prefix))
		{
			$this->table_prefix = $params->prefix;
			$this->db->table_prefix = $params->prefix;
		}

		// Set the connection name property, if applicable
		if (isset($params->name))
		{
			$this->conn_name = $params->name;
		}

		// Instantiate the Query Parser
		$this->parser = new Query_Parser();

		// Make things just slightly shorter
		$this->sql =& $this->db->sql;
	}
	
	/**
	 * Create the dsn for connection to the database
	 *
	 * @param string $dbtype
	 * @param object $params
	 * @return string
	 */
	private function _connect($dbtype, &$params)
	{
		// Let the connection work with 'conn_db' or 'database'
		if (isset($params->database))
		{
			$params->conn_db = $params->database;
		}

		// Add the driver type to the dsn
		$dsn = ($dbtype !== 'firebird' && $dbtype !== 'sqlite')
			? strtolower($dbtype).':'
			: '';

		// Make sure the class exists
		if ( ! class_exists($dbtype))
		{
			throw new BadDBDriverException('Database driver does not exist, or is not supported');
		}

		// Create the dsn for the database to connect to
		switch($dbtype)
		{
			default:
				$dsn .= "dbname={$params->conn_db}";

				if ( ! empty($params->host))
				{
					$dsn .= ";host={$params->host}";
				}

				if ( ! empty($params->port))
				{
					$dsn .= ";port={$params->port}";
				}

			break;

			case "sqlite":
				$dsn .= $params->file;
			break;

			case "firebird":
				$dsn = "{$params->host}:{$params->file}";
			break;
		}
		
		return $dsn;
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
		$safe_array = array_map(array($this->db, 'quote_ident'), $fields_array);

		unset($fields_array);

		// Join the strings back together
		for($i = 0, $c = count($safe_array); $i < $c; $i++)
		{
			if (is_array($safe_array[$i]))
			{
				$safe_array[$i] = implode(' AS ', $safe_array[$i]);
			}
		}

		$this->select_string .= implode(',', $safe_array);

		unset($safe_array);

		return $this;
	}

	// --------------------------------------------------------------------------

	/**
	 * Method to simplify select_ methods
	 *
	 * @param string $field
	 * @param string $as
	 * @return string
	 */
	private function _select($field, $as = FALSE)
	{
		// Escape the identifiers
		$field = $this->quote_ident($field);

		$as = ($as !== FALSE)
			? $this->quote_ident($as)
			: $field;

		return "({$field}) AS {$as} ";
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
	 * Specify the database table to select from
	 *
	 * @param string $tblname
	 * @return $this
	 */
	public function from($tblname)
	{
		// Split identifiers on spaces
		$ident_array = explode(' ', trim($tblname));
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
	 * @return $this
	 */
	private function _like($field, $val, $pos, $like='LIKE', $conj='AND')
	{
		$field = $this->quote_ident($field);

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

		$this->query_map[] = array(
			'type' => 'like',
			'conjunction' => (empty($this->query_map)) ? ' WHERE ' : " {$conj} ",
			'string' => $l
		);

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
	 * Simplify building having clauses
	 *
	 * @param mixed $key
	 * @param mixed $val
	 * @param string $conj
	 * @return $this
	 */
	private function _having($key, $val=array(), $conj='AND')
	{
		$where = $this->_where($key, $val);

		// Create key/value placeholders
		foreach($where as $f => $val)
		{
			// Split each key by spaces, in case there
			// is an operator such as >, <, !=, etc.
			$f_array = explode(' ', trim($f));

			$item = $this->quote_ident($f_array[0]);

			// Simple key value, or an operator
			$item .= (count($f_array) === 1) ? '=?' : " {$f_array[1]} ?";

			// Put in the query map for select statements
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
	 * Do all the repeditive stuff for where/having type methods
	 *
	 * @param mixed $key
	 * @param mixed $val
	 * @return array
	 */
	private function _where($key, $val=array())
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
	 * @return $this
	 */
	private function _where_string($key, $val=array(), $conj='AND')
	{
		$where = $this->_where($key, $val);

		// Create key/value placeholders
		foreach($where as $f => $val)
		{
			// Split each key by spaces, in case there
			// is an operator such as >, <, !=, etc.
			$f_array = explode(' ', trim($f));

			$item = $this->quote_ident($f_array[0]);

			// Simple key value, or an operator
			$item .= (count($f_array) === 1) ? '=?' : " {$f_array[1]} ?";

			// Put in the query map for select statements
			$this->query_map[] = array(
				'type' => 'where',
				'conjunction' => ( ! empty($this->query_map)) ? " {$conj} " : ' WHERE ',
				'string' => $item
			);
		}

		return $this;
	}

	// --------------------------------------------------------------------------

	/**
	 * Simplify where_in methods
	 *
	 * @param mixed $key
	 * @param mixed $val
	 * @param string
	 * @param string
	 * @return $this
	 */
	private function _where_in($key, $val=array(), $in='IN', $conj='AND')
	{
		$key = $this->quote_ident($key);
		$params = array_fill(0, count($val), '?');

		foreach($val as $v)
		{
			$this->where_values[] = $v;
		}

		$string = $key . " {$in} (".implode(',', $params).') ';

		$this->query_map[] = array(
			'type' => 'where_in',
			'conjunction' => ( ! empty($this->query_map)) ? " {$conj} " : ' WHERE ',
			'string' => $string
		);

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
	 * @return $this
	 */
	public function where($key, $val=array())
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
		elseif ( ! is_scalar($key))
		{
			foreach($key as $k => $v)
			{
				$this->set_array_keys[] = $k;
				$this->values[] = $v;
			}
		}

		// Use the keys of the array to make the insert/update string
		// Escape the field names
		$this->set_array_keys = array_map(array($this->db, 'quote_ident'), $this->set_array_keys);

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
				$parts['combined'][$i] = $this->quote_ident($parts['combined'][$i]);
			}
		}

		$parsed_condition = implode('', $parts['combined']);

		$condition = $table . ' ON ' . $parsed_condition;

		$this->query_map[] = array(
			'type' => 'join',
			'conjunction' => strtoupper($type).' JOIN ',
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
			$this->group_array = array_map(array($this->db, 'quote_ident'), $field);
		}
		else
		{
			$this->group_array[] = $this->quote_ident($field);
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
		$field = $this->quote_ident($field);
		$this->order_array[$field] = $type;

		$order_clauses = array();

		// Flatten key/val pairs into an array of space-separated pairs
		foreach($this->order_array as $k => $v)
		{
			$order_clauses[] = $k . ' ' . strtoupper($v);
		}

		// Set the final string
		$this->order_string = (empty($rand))
			? ' ORDER BY '.implode(',', $order_clauses)
			: ' ORDER BY'.$rand;

		return $this;
	}

	// --------------------------------------------------------------------------

	/**
	 * Set a limit on the current sql statement
	 *
	 * @param int $limit
	 * @param int $offset
	 * @return string
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
			'conjunction' => '',
			'string' => ' ('
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
			'string' => ' ) '
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

		// Do prepared statements for anything involving a "where" clause
		if ( ! empty($this->query_map) || ! empty($this->having_map))
		{
			return $this->_run("get", $table);
		}
		else
		{
			return $this->_run("get", $table, TRUE);
		}
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
		$sql = 'SELECT * FROM '.$this->quote_table($table);
		$res = $this->query($sql);
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

		// Do prepared statements for anything involving a "where" clause
		if ( ! empty($this->query_map))
		{
			$result = $this->_run('get', $table);
		}
		else
		{
			// Otherwise, a simple query will do.
			$result =  $this->_run('get', $table, TRUE);
		}

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
	// ! Query Returning Methods
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
	 * Returns the generated 'insert' sql query
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
	 * Returns the generated 'insert' sql query
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

	/**
	 * Helper function for returning sql strings
	 *
	 * @param string $type
	 * @param string $table
	 * @param bool
	 * @resturn string
	 */
	private function _get_compile($type, $table, $reset)
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
	// ! Miscellaneous Methods
	// --------------------------------------------------------------------------

	/**
	 * Clear out the class variables, so the next query can be run
	 *
	 * @return void
	 */
	public function reset_query()
	{
		foreach($this as $name => $var)
		{
			$skip = array('db','sql','queries','table_prefix','parser','conn_name');

			// Skip properties that are needed for every query
			if (in_array($name, $skip))
			{
				continue;
			}

			// Nothing query-generation related is safe!
			$this->$name = NULL;

			// Set empty arrays
			$this->values = array();

			// Set select string as an empty string, for proper handling
			// of the 'distinct' keyword
			$this->select_string = '';
		}
	}

	// --------------------------------------------------------------------------

	/**
	 * Executes the compiled query
	 *
	 * @param string $type
	 * @param string $table
	 * @param bool $simple
	 * @return mixed
	 */
	private function _run($type, $table, $simple=FALSE)
	{
		$sql = $this->_compile($type, $table);
		$vals = array_merge($this->values, (array) $this->where_values);

		$res = ($simple)
			? $this->query($sql)
			: $this->prepare_execute($sql, $vals);

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
	 */
	public function __call($name, $params)
	{
		if (method_exists($this->db, $name))
		{
			return call_user_func_array(array($this->db, $name), $params);
		}

		return NULL;
	}

	// --------------------------------------------------------------------------

	/**
	 * String together the sql statements for sending to the db
	 *
	 * @param string $type
	 * @param string $table
	 * @return $string
	 */
	private function _compile($type='', $table='')
	{
		$sql = '';

		$table = $this->quote_table($table);

		switch($type)
		{
			default:
			case "get":
				$sql = "SELECT * FROM {$this->from_string}";

				// Set the select string
				if ( ! empty($this->select_string))
				{
					// Replace the star with the selected fields
					$sql = str_replace('*', $this->select_string, $sql);
				}
			break;

			case "insert":
				$param_count = count($this->set_array_keys);
				$params = array_fill(0, $param_count, '?');
				$sql = "INSERT INTO {$table} ("
					. implode(',', $this->set_array_keys) .
					') VALUES ('.implode(',', $params).')';
			break;

			case "update":
				$sql = "UPDATE {$table} SET {$this->set_string}";
			break;

			case "delete":
				$sql = "DELETE FROM {$table}";
			break;
		}

		// Set the where clause
		if ( ! empty($this->query_map))
		{
			foreach($this->query_map as $q)
			{
				$sql .= $q['conjunction'] . $q['string'];
			}
		}

		// Set the group_by clause
		if ( ! empty($this->group_string))
		{
			$sql .= $this->group_string;
		}

		// Set the order_by clause
		if ( ! empty($this->order_string))
		{
			$sql .= $this->order_string;
		}

		// Set the having clause
		if ( ! empty($this->having_map))
		{
			foreach($this->having_map as $h)
			{
				$sql .= $h['conjunction'] . $h['string'];
			}
		}
		
		// Set the limit via the class variables
		if (isset($this->limit) && is_numeric($this->limit))
		{
			$sql = $this->sql->limit($sql, $this->limit, $this->offset);
		}

		// Add the query to the list of executed queries
		$this->queries[] = $sql;

		// Set the last query to get rowcounts properly
		$this->db->last_query = $sql;

		// echo $sql . '<br />';

		return $sql;
	}
}
// End of query_builder.php