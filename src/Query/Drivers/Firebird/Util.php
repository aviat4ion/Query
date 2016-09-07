<?php
/**
 * Query
 *
 * Free Query Builder / Database Abstraction Layer
 *
 * @package		Query
 * @author		Timothy J. Warren
 * @copyright	Copyright (c) 2012 - 2015
 * @link 		https://github.com/aviat4ion/Query
 * @license		http://philsturgeon.co.uk/code/dbad-license
 */

// --------------------------------------------------------------------------

namespace Query\Drivers\Firebird;

/**
 * Firebird-specific backup, import and creation methods
 *
 * @package Query
 * @subpackage Drivers
 */
class Util extends \Query\AbstractUtil {

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
	public function create_table($name, $fields, array $constraints=[], $if_not_exists=FALSE)
	{
		return parent::create_table($name, $fields, $constraints, FALSE);
	}

	/**
	 * Drop the selected table
	 *
	 * @param string $name
	 * @return string
	 */
	public function delete_table($name)
	{
		return 'DROP TABLE '.$this->get_driver()->quote_table($name);
	}

	// --------------------------------------------------------------------------

	/**
	 * Create an SQL backup file for the current database's structure
	 *
	 * @param string $db_path
	 * @param string $new_file
	 * @return string
	 */
	public function backup_structure()
	{
		list($db_path, $new_file) = func_get_args();
		return ibase_backup($this->get_driver()->get_service(), $db_path, $new_file, \IBASE_BKP_METADATA_ONLY);
	}

	// --------------------------------------------------------------------------

	/**
	 * Create an SQL backup file for the current database's data
	 *
	 * @param array $exclude
	 * @param bool $system_tables
	 * @return string
	 */
	public function backup_data($exclude=[], $system_tables=FALSE)
	{
		// Determine which tables to use
		$tables = $this->get_driver()->get_tables();
		if($system_tables == TRUE)
		{
			$tables = array_merge($tables, $this->get_driver()->get_system_tables());
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
			$res = $this->get_driver()->query($sql);
			$obj_res = $res->fetchAll(\PDO::FETCH_ASSOC);

			// Don't add to the file if the table is empty
			if (count($obj_res) < 1)
			{
				continue;
			}

			// Nab the column names by getting the keys of the first row
			$columns = @array_keys($obj_res[0]);

			$insert_rows = [];

			// Create the insert statements
			foreach($obj_res as $row)
			{
				$row = array_values($row);

				// Quote values as needed by type
				if(stripos($t, 'RDB$') === FALSE)
				{
					$row = array_map([$this->get_driver(), 'quote'], $row);
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