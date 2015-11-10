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
 * Convenience class for creating sql queries - also the class that
 * instantiates the specific db driver
 *
 * @package Query
 * @subpackage Query_Builder
 */
class QueryBuilder extends AbstractQueryBuilder implements QueryBuilderInterface {

	/**
	 * String class values to be reset
	 *
	 * @var array
	 */
	private $string_vars = array(
		'select_string',
		'from_string',
		'set_string',
		'order_string',
		'group_string',
		'limit',
		'offset',
		'explain',
	);

	/**
	 * Array class variables to be reset
	 *
	 * @var array
	 */
	private $array_vars = array(
		'set_array_keys',
		'order_array',
		'group_array',
		'values',
		'where_values',
		'query_map',
		'having_map'
	);

	// --------------------------------------------------------------------------
	// ! Methods
	// --------------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @param DriverInterface $db
	 * @param QueryParser $parser
	 */
	public function __construct(DriverInterface $db, QueryParser $parser)
	{
		// Inject driver and parser
		$this->db = $db;
		$this->parser = $parser;

		$this->queries['total_time'] = 0;

		// Alias driver sql and util classes
		$this->sql = $this->db->get_sql();
		$this->util = $this->db->get_util();
	}

	// --------------------------------------------------------------------------

	/**
	 * Destructor
	 * @codeCoverageIgnore
	 */
	public function __destruct()
	{
		$this->db = NULL;
	}

	// --------------------------------------------------------------------------

	/**
	 * Calls a function further down the inheritence chain
	 *
	 * @param string $name
	 * @param array $params
	 * @return mixed
	 * @throws \BadMethodCallException
	 */
	public function __call($name, $params)
	{
		// Allow camel-case method calls
		$snake_name = \from_camel_case($name);

		foreach(array($this, $this->db) as $object)
		{
			foreach(array($name, $snake_name) as $method_name)
			{
				if (method_exists($object, $method_name))
				{
					return call_user_func_array(array($object, $method_name), $params);
				}
			}

		}

		throw new \BadMethodCallException("Method does not exist");
	}

	// --------------------------------------------------------------------------
	// ! Select Queries
	// --------------------------------------------------------------------------

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
	 * @param string|FALSE $as
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
	 * @param string|bool $as
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
	 * @param string|bool $as
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
	 * @param string|bool $as
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
		$ident_array = explode(' ', \mb_trim($tblname));
		$ident_array = array_map('\\mb_trim', $ident_array);

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
		$this->_mixed_set($this->set_array_keys, $key, $val, self::KEY);
		$this->_mixed_set($this->values, $key, $val, self::VALUE);

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
		$parsed_condition = $this->parser->compile_join($condition);
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
		// When ordering by random, do an ascending order if the driver
		// doesn't support random ordering
		if (stripos($type, 'rand') !== FALSE)
		{
			$rand = $this->sql->random();
			$type = ($rand !== FALSE) ? $rand : 'ASC';
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
		$this->order_string = ( ! isset($rand))
			? "\nORDER BY ".implode(', ', $order_clauses)
			: "\nORDER BY".$rand;

		return $this;
	}

	// --------------------------------------------------------------------------

	/**
	 * Set a limit on the current sql statement
	 *
	 * @param int $limit
	 * @param int|bool $offset
	 * @return Query_Builder
	 */
	public function limit($limit, $offset=FALSE)
	{
		$this->limit = (int) $limit;
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
	 * @param int|bool $limit
	 * @param int|bool $offset
	 * @return \PDOStatement
	 */
	public function get($table='', $limit=FALSE, $offset=FALSE)
	{
		// Set the table
		if ( ! empty($table)) $this->from($table);

		// Set the limit, if it exists
		if (is_int($limit)) $this->limit($limit, $offset);

		return $this->_run("get", $table);
	}

	// --------------------------------------------------------------------------

	/**
	 * Convenience method for get() with a where clause
	 *
	 * @param string $table
	 * @param array $where
	 * @param int|bool $limit
	 * @param int|bool $offset
	 * @return \PDOStatement
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
		if ( ! empty($table)) $this->from($table);

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
	 * @return \PDOStatement
	 */
	public function insert($table, $data=array())
	{
		// No use duplicating logic!
		if ( ! empty($data)) $this->set($data);

		return $this->_run("insert", $table);
	}

	// --------------------------------------------------------------------------

	/**
	 * Creates and executes a batch insertion query
	 *
	 * @param string $table
	 * @param array $data
	 * @return \PDOStatement
	 */
	public function insert_batch($table, $data=array())
	{
		// Get the generated values and sql string
		list($sql, $data) = $this->db->insert_batch($table, $data);

		return ( ! is_null($sql))
			? $this->_run('', $table, $sql, $data)
			: NULL;
	}

	// --------------------------------------------------------------------------

	/**
	 * Creates an update clause, and executes it
	 *
	 * @param string $table
	 * @param mixed $data
	 * @return \PDOStatement
	 */
	public function update($table, $data=array())
	{
		// No use duplicating logic!
		if ( ! empty($data)) $this->set($data);

		return $this->_run("update", $table);
	}

	// --------------------------------------------------------------------------

	/**
	 * Deletes data from a table
	 *
	 * @param string $table
	 * @param mixed $where
	 * @return \PDOStatement
	 */
	public function delete($table, $where='')
	{
		// Set the where clause
		if ( ! empty($where)) $this->where($where);

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
		if ( ! empty($table)) $this->from($table);

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
		// Reset strings and booleans
		foreach($this->string_vars as $var)
		{
			$this->$var = NULL;
		}

		// Reset arrays
		foreach($this->array_vars as $var)
		{
			$this->$var = array();
		}
	}
}
// End of query_builder.php