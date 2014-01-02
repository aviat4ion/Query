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
 * MySQL-specific backup, import and creation methods
 *
 * @package Query
 * @subpackage Drivers
 */
class MySQL_Util extends DB_Util {

	/**
	 * Save a reference to the current connection object
	 *
	 * @param object &$conn
	 * @return void
	 */
	public function __construct(&$conn)
	{
		parent::__construct($conn);
	}
	
	// --------------------------------------------------------------------------
	
	/**
 	 * Convienience public function for creating a new MySQL table
 	 *
 	 * @param string $name
 	 * @param array $columns
 	 * @param array $constraints
 	 * @param array $indexes
 	 *
 	 * @return string
 	 */
	public function create_table($name, $columns, array $constraints=array(), array $indexes=array())
	{
		$column_array = array();

		// Reorganize into an array indexed with column information
		// Eg $column_array[$colname] = array(
		// 		'type' => ...,
		// 		'constraint' => ...,
		// 		'index' => ...,
		// )
		foreach($columns as $colname => $type)
		{
			if(is_numeric($colname))
			{
				$colname = $type;
			}

			$column_array[$colname] = array();
			$column_array[$colname]['type'] = ($type !== $colname) ? $type : '';
		}

		if( ! empty($constraints))
		{
			foreach($constraints as $col => $const)
			{
				$column_array[$col]['constraint'] = "{$const} ({$col})";
			}
		}

		// Join column definitons together
		$columns = array();
		foreach($column_array as $n => $props)
		{
			$n = trim($n, '`');

			$str = "`{$n}` ";
			$str .= (isset($props['type'])) ? "{$props['type']} " : "";

			$columns[] = $str;
		}

		// Add constraints
		foreach($column_array as $n => $props)
		{
			if (isset($props['constraint']))
			{
				$columns[] = $props['constraint'];
			}
		}

		// Generate the sql for the creation of the table
		$sql = "CREATE TABLE IF NOT EXISTS `{$name}` (";
		$sql .= implode(", ", $columns);
		$sql .= ")";

		return $sql;
	}

	// --------------------------------------------------------------------------

	/**
	 * Convience public function for droping a table
	 *
	 * @param string $name
	 * @return  string
	 */
	public function delete_table($name)
	{
		return "DROP TABLE `{$name}`";
	}
	
	// --------------------------------------------------------------------------

	/**
	 * Create an SQL backup file for the current database's structure
	 *
	 * @return string
	 */
	public function backup_structure()
	{
		$string = array();
	
		// Get databases
		$dbs = $this->get_dbs();
		
		foreach($dbs as &$d)
		{
			// Skip built-in dbs
			if ($d == 'mysql')
			{
				continue;
			}
		
			// Get the list of tables
			$tables = $this->driver_query("SHOW TABLES FROM `{$d}`");
			
			foreach($tables as &$table)
			{
				$array = $this->driver_query("SHOW CREATE TABLE `{$d}`.`{$table}`", FALSE);
				$string[] = $array[0]['Create Table'];
			}
		}
	
		return implode("\n\n", $string);
	}

	// --------------------------------------------------------------------------

	/**
	 * Create an SQL backup file for the current database's data
	 *
	 * @param array $exclude
	 * @return string
	 */
	public function backup_data($exclude=array())
	{
		$tables = $this->get_tables();
		
		// Filter out the tables you don't want
		if( ! empty($exclude))
		{
			$tables = array_diff($tables, $exclude);
		}
		
		$output_sql = '';
		
		// Select the rows from each Table
		foreach($tables as $t)
		{
			$sql = "SELECT * FROM `{$t}`";
			$res = $this->query($sql);
			$rows = $res->fetchAll(PDO::FETCH_ASSOC);
			
			$res = NULL;
			
			// Skip empty tables
			if (count($rows) < 1) continue;
			
			// Nab the column names by getting the keys of the first row
			$columns = @array_keys($rows[0]);

			$insert_rows = array();
			
			// Create the insert statements
			foreach($rows as $row)
			{
				$row = array_values($row);
				
				// Workaround for Quercus
				foreach($row as &$r)
				{
					$r = $this->quote($r);
				}
				$row = array_map('trim', $row);

				$row_string = 'INSERT INTO `'.trim($t).'` (`'.implode('`,`', $columns).'`) VALUES ('.implode(',', $row).');';

				$row = NULL;

				$insert_rows[] = $row_string;
			}
			
			$obj_res = NULL;

			$output_sql .= "\n\n".implode("\n", $insert_rows)."\n";
		}
	
		return $output_sql;
	}
}
// End of mysql_util.php
