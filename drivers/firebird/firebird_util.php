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
 * Firebird-specific backup, import and creation methods
 *
 * @package Query
 * @subpackage Drivers
 * @method array get_system_tables()
 * @method array get_tables()
 * @method object query(string $sql)
 */
class Firebird_Util extends DB_Util {

	/**
	 * Create an SQL backup file for the current database's structure
	 * @codeCoverageIgnore
	 * @return string
	 */
	public function backup_structure()
	{
		// TODO Implement Backup structure function
		return '';
	}

	// --------------------------------------------------------------------------

	/**
	 * Create an SQL backup file for the current database's data
	 *
	 * @codeCoverageIgnore
	 * @param array $exclude
	 * @param bool $system_tables
	 * @return string
	 */
	public function backup_data($exclude=array(), $system_tables=FALSE)
	{
		// Determine which tables to use
		if($system_tables == TRUE)
		{
			$tables = array_merge($this->get_system_tables(), $this->get_tables());
		}
		else
		{
			$tables = $this->get_tables();
		}

		// Filter out the tables you don't want
		if( ! empty($exclude))
		{
			$tables = array_diff($tables, $exclude);
		}

		$output_sql = '';

		// Get the data for each object
		foreach($tables as $t)
		{
			$sql = 'SELECT * FROM "'.trim($t).'"';
			$res = $this->query($sql);
			$obj_res = $res->fetchAll(PDO::FETCH_ASSOC);

			// Don't add to the file if the table is empty
			if (count($obj_res) < 1) continue;

			// Nab the column names by getting the keys of the first row
			$columns = @array_keys($obj_res[0]);

			$insert_rows = array();

			// Create the insert statements
			foreach($obj_res as $row)
			{
				$row = array_values($row);

				// Quote values as needed by type
				if(stripos($t, 'RDB$') === FALSE)
				{
					$row = array_map(array(&$this, 'quote'), $row);
					$row = array_map('trim', $row);
				}

				$row_string = 'INSERT INTO "'.trim($t).'" ("'.implode('","', $columns).'") VALUES ('.implode(',', $row).');';

				$row = NULL;

				$insert_rows[] = $row_string;
			}

			$output_sql .= "\n\nSET TRANSACTION;\n".implode("\n", $insert_rows)."\nCOMMIT;";
		}

		return $output_sql;
	}
}
// End of firebird_util.php