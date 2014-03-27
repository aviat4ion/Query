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
 * Base class for query builder - defines the internal methods
 *
 * @package Query
 * @subpackage Query
 */
abstract class Abstract_Query_Builder implements iQuery_Builder {

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
	 * Simplify building having clauses
	 *
	 * @param mixed $key
	 * @param mixed $val
	 * @param string $conj
	 * @return $this
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
	 * @return $this
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

			// Put in the query map for select statements
			$this->query_map[] = array(
				'type' => 'where',
				'conjunction' => $conj,
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
	 * @param string $in - The (not) in fragment
	 * @param string $conj - The where in conjunction
	 * @return $this
	 */
	protected function _where_in($key, $val=array(), $in='IN', $conj='AND')
	{
		$key = $this->db->quote_ident($key);
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
	 * Simplify 'like' methods
	 *
	 * @param string $field
	 * @param mixed $val
	 * @param string $pos
	 * @param string $like
	 * @param string $conj
	 * @return $this
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

	// --------------------------------------------------------------------------

	/**
	 * Executes the compiled query
	 *
	 * @param string $type
	 * @param string $table
	 * @param string $sql
	 * @param mixed $vals
	 * @return mixed
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

		$evals = (is_array($vals)) ? $vals : array();

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

		// Add the interpreted query to the list of executed queries
		foreach($evals as $k => &$v)
		{
			$v = ( ! is_numeric($v)) ? htmlentities($this->db->quote($v), ENT_NOQUOTES, 'utf-8', FALSE)  : $v;
		}
		$esql = str_replace('?', "%s", $sql);
		array_unshift($vals, $esql);
		array_unshift($evals, $esql);


		$this->queries[] = array(
			'time' => $total_time,
			'sql' => call_user_func_array('sprintf', $evals),
		);
		$this->queries['total_time'] += $total_time;

		array_shift($vals);

		// Set the last query to get rowcounts properly
		$this->db->last_query = $sql;

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

	// --------------------------------------------------------------------------

	/**
	 * Sub-method for generating sql strings
	 *
	 * @param string $type
	 * @param string $table
	 * @return $string
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

		// Set the where clause
		if ( ! empty($this->query_map))
		{
			foreach($this->query_map as $k => $q)
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

		// See what needs to happen to only return the query plan
		if (isset($this->explain) && $this->explain === TRUE)
		{
			$sql = $this->sql->explain($sql);
		}

		return $sql;
	}
}
// End of abstract_query_builder.php
