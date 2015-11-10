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

namespace Query\Drivers\Mysql;

/**
 * MySQL-specific backup, import and creation methods
 *
 * @package Query
 * @subpackage Drivers
 */
class Util extends \Query\AbstractUtil {

	/**
	 * Create an SQL backup file for the current database's structure
	 *
	 * @return string
	 */
	public function backup_structure()
	{
		$string = array();

		// Get databases
		$dbs = $this->get_driver()->get_dbs();

		foreach($dbs as &$d)
		{
			// Skip built-in dbs
			if ($d == 'mysql') continue;

			// Get the list of tables
			$tables = $this->get_driver()->driver_query("SHOW TABLES FROM `{$d}`", TRUE);

			foreach($tables as $table)
			{
				$array = $this->get_driver()->driver_query("SHOW CREATE TABLE `{$d}`.`{$table}`", FALSE);
				$row = current($array);

				if ( ! isset($row['Create Table'])) continue;


				$string[] = $row['Create Table'];
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
		$tables = $this->get_driver()->get_tables();

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
			$res = $this->get_driver()->query($sql);
			$rows = $res->fetchAll(\PDO::FETCH_ASSOC);

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
					$r = $this->get_driver()->quote($r);
				}
				$row = array_map('trim', $row);

				$row_string = 'INSERT INTO `'.trim($t).'` (`'.implode('`,`', $columns).'`) VALUES ('.implode(',', $row).');';

				$row = NULL;

				$insert_rows[] = $row_string;
			}

			$output_sql .= "\n\n".implode("\n", $insert_rows)."\n";
		}

		return $output_sql;
	}
}
// End of mysql_util.php
