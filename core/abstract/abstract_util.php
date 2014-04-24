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

namespace Query\Driver;

/**
 * Abstract class defining database / table creation methods
 *
 * @package Query
 * @subpackage Drivers
 * @method string quote_ident(string $sql)
 * @method string quote_table(string $sql)
 */
abstract class Abstract_Util {

	/**
	 * Reference to the current connection object
	 */
	private $conn;

	/**
	 * Save a reference to the connection object for later use
	 *
	 * @param Driver_Interface $conn
	 */
	public function __construct(Driver_Interface $conn)
	{
		$this->conn = $conn;
	}

	// --------------------------------------------------------------------------

	/**
	 * Enable calling driver methods
	 *
	 * @param string $method
	 * @param array $args
	 * @return mixed
	 */
	public function __call($method, $args)
	{
		return call_user_func_array(array($this->conn, $method), $args);
	}

	// --------------------------------------------------------------------------
	// ! Abstract Methods
	// --------------------------------------------------------------------------

	/**
	 * Convenience public function to generate sql for creating a db table
	 *
	 * @deprecated Use the table builder class instead
	 * @param string $name
	 * @param array $fields
	 * @param array $constraints
	 * @param bool $if_not_exists
	 * @return string
	 */
	public function create_table($name, $fields, array $constraints=array(), $if_not_exists=TRUE)
	{
		$column_array = array();

		$exists_str = ($if_not_exists) ? ' IF NOT EXISTS ' : ' ';

		// Reorganize into an array indexed with column information
		// Eg $column_array[$colname] = array(
		// 		'type' => ...,
		// 		'constraint' => ...,
		// 		'index' => ...,
		// )
		foreach($fields as $colname => $type)
		{
			$column_array[$colname] = array();
			$column_array[$colname]['type'] = ($type !== $colname) ? $type : '';
		}

		if( ! empty($constraints))
		{
			foreach($constraints as $col => $const)
			{
				$column_array[$col]['constraint'] = $const;
			}
		}

		// Join column definitions together
		$columns = array();
		foreach($column_array as $n => $props)
		{
			$str = $this->quote_ident($n);
			$str .= (isset($props['type'])) ? " {$props['type']}" : "";
			$str .= (isset($props['constraint'])) ? " {$props['constraint']}" : "";

			$columns[] = $str;
		}

		// Generate the sql for the creation of the table
		$sql = 'CREATE TABLE'.$exists_str.$this->quote_table($name).' (';
		$sql .= implode(', ', $columns);
		$sql .= ')';

		return $sql;
	}
	
	// --------------------------------------------------------------------------

	/**
	 * Drop the selected table
	 *
	 * @param string $name
	 * @return string
	 */
	public function delete_table($name)
	{
		return 'DROP TABLE IF EXISTS '.$this->quote_table($name);
	}
	
	// --------------------------------------------------------------------------

	/**
	 * Return an SQL file with the database table structure
	 *
	 * @abstract
	 * @return string
	 */
	abstract public function backup_structure();
	
	// --------------------------------------------------------------------------

	/**
	 * Return an SQL file with the database data as insert statements
	 *
	 * @abstract
	 * @return string
	 */
	abstract public function backup_data();

}
// End of abstract_util.php