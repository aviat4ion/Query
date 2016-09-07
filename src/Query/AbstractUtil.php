<?php
/**
 * Query
 *
 * SQL Query Builder / Database Abstraction Layer
 *
 * PHP version 5.4
 *
 * @package     Query
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2012 - 2015 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link        https://git.timshomepage.net/aviat4ion/Query
 */


// --------------------------------------------------------------------------

namespace Query;

// --------------------------------------------------------------------------

/**
 * Abstract class defining database / table creation methods
 *
 * @package Query
 * @subpackage Drivers
 * @method string quote_ident(string $sql)
 * @method string quote_table(string $sql)
 */
abstract class AbstractUtil {

	/**
	 * Reference to the current connection object
	 */
	private $conn;

	/**
	 * Save a reference to the connection object for later use
	 *
	 * @param DriverInterface $conn
	 */
	public function __construct(DriverInterface $conn)
	{
		$this->conn = $conn;
	}

	// --------------------------------------------------------------------------

	/**
	 * Get the driver object for the current connection
	 *
	 * @return Driver_Interface
	 */
	public function get_driver()
	{
		return $this->conn;
	}

	// --------------------------------------------------------------------------

	/**
	 * Convenience public function to generate sql for creating a db table
	 *
	 * @param string $name
	 * @param array $fields
	 * @param array $constraints
	 * @param bool $if_not_exists
	 * @return string
	 */
	public function create_table($name, $fields, array $constraints=[], $if_not_exists=TRUE)
	{
		$exists_str = ($if_not_exists) ? ' IF NOT EXISTS ' : ' ';

		// Reorganize into an array indexed with column information
		// Eg $column_array[$colname] = array(
		// 		'type' => ...,
		// 		'constraint' => ...,
		// 		'index' => ...,
		// )
		$column_array = \array_zipper([
			'type' => $fields,
			'constraint' => $constraints
		]);

		// Join column definitions together
		$columns = [];
		foreach($column_array as $n => $props)
		{
			$str = $this->get_driver()->quote_ident($n);
			$str .= (isset($props['type'])) ? " {$props['type']}" : "";
			$str .= (isset($props['constraint'])) ? " {$props['constraint']}" : "";

			$columns[] = $str;
		}

		// Generate the sql for the creation of the table
		$sql = 'CREATE TABLE'.$exists_str.$this->get_driver()->quote_table($name).' (';
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
		return 'DROP TABLE IF EXISTS '.$this->get_driver()->quote_table($name);
	}


	// --------------------------------------------------------------------------
	// ! Abstract Methods
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