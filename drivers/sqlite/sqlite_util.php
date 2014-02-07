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
 * SQLite-specific backup, import and creation methods
 *
 * @package Query
 * @subpackage Drivers
 */
class SQLite_Util extends DB_Util {

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
	 * Convenience public function to create a new table
	 *
	 * @param string $name //Name of the table
	 * @param array $columns //columns as straight array and/or column => type pairs
	 * @param array $constraints // column => constraint pairs
	 * @param array $indexes // column => index pairs
	 * @return  string
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
				$column_array[$col]['constraint'] = $const;
			}
		}

		// Join column definitons together
		$columns = array();
		foreach($column_array as $n => $props)
		{
			$str = "{$n} ";
			$str .= (isset($props['type'])) ? "{$props['type']} " : "";
			$str .= (isset($props['constraint'])) ? $props['constraint'] : "";

			$columns[] = $str;
		}

		// Generate the sql for the creation of the table
		$sql = "CREATE TABLE IF NOT EXISTS \"{$name}\" (";
		$sql .= implode(", ", $columns);
		$sql .= ")";

		return $sql;
	}

	// --------------------------------------------------------------------------

	/**
	 * SQL to drop the specified table
	 *
	 * @param string $name
	 * @return string
	 */
	public function delete_table($name)
	{
		return 'DROP TABLE IF EXISTS "'.$name.'"';
	}
	
	// --------------------------------------------------------------------------

	/**
	 * Create an SQL backup file for the current database's data
	 *
	 * @param array $excluded
	 * @return string
	 */
	public function backup_data($excluded=array())
	{
		// Get a list of all the objects
		$sql = 'SELECT "name" FROM "sqlite_master"';

		if( ! empty($excluded))
		{
			$sql .= ' WHERE NOT IN("'.implode('","', $excluded).'")';
		}

		$res = $this->query($sql);
		$result = $res->fetchAll(PDO::FETCH_ASSOC);

		unset($res);

		$output_sql = '';

		// Get the data for each object
		foreach($result as $r)
		{
			$sql = 'SELECT * FROM "'.$r['name'].'"';
			$res = $this->query($sql);
			$obj_res = $res->fetchAll(PDO::FETCH_ASSOC);

			unset($res);
			
			// If the row is empty, continue;
			if (empty($obj_res)) continue;

			// Nab the column names by getting the keys of the first row
			$columns = array_keys(current($obj_res));

			$insert_rows = array();

			// Create the insert statements
			foreach($obj_res as $row)
			{
				$row = array_values($row);

				// Quote values as needed by type
				for($i=0, $icount=count($row); $i<$icount; $i++)
				{
					$row[$i] = (is_numeric($row[$i])) ? $row[$i] : $this->quote($row[$i]);
				}

				$row_string = 'INSERT INTO "'.$r['name'].'" ("'.implode('","', $columns).'") VALUES ('.implode(',', $row).');';

				unset($row);

				$insert_rows[] = $row_string;
			}

			unset($obj_res);

			$output_sql .= "\n\n".implode("\n", $insert_rows);
		}

		return $output_sql;
	}

	// --------------------------------------------------------------------------

	/**
	 * Create an SQL backup file for the current database's structure
	 *
	 * @return string
	 */
	public function backup_structure()
	{
		// Fairly easy for SQLite...just query the master table
		$sql = 'SELECT "sql" FROM "sqlite_master"';
		$res = $this->query($sql);
		$result = $res->fetchAll(PDO::FETCH_ASSOC);

		$sql_array = array();

		foreach($result as $r)
		{
			$sql_array[] = $r['sql'];
		}

		$sql_structure = implode(";\n", $sql_array) . ";";

		return $sql_structure;
	}
}
// End of sqlite_util.php