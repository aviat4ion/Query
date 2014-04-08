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
 * SQLite-specific backup, import and creation methods
 *
 * @package Query
 * @subpackage Drivers
 * @method mixed query(string $sql)
 * @method string quote(string $str)
 */
class SQLite_Util extends Abstract_Util {

	/**
	 * Create an SQL backup file for the current database's data
	 *
	 * @param array $excluded
	 * @return string
	 */
	public function backup_data($excluded=array())
	{
		// Get a list of all the objects
		$sql = 'SELECT DISTINCT "name"
				FROM "sqlite_master"
				WHERE "type"=\'table\'';

		if( ! empty($excluded))
		{
			$sql .= " AND \"name\" NOT IN('".implode("','", $excluded)."')";
		}

		$res = $this->query($sql);
		$result = $res->fetchAll(\PDO::FETCH_ASSOC);

		unset($res);

		$output_sql = '';

		// Get the data for each object
		foreach($result as $r)
		{
			$sql = 'SELECT * FROM "'.$r['name'].'"';
			$res = $this->query($sql);
			$obj_res = $res->fetchAll(\PDO::FETCH_ASSOC);

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
		$result = $res->fetchAll(\PDO::FETCH_ASSOC);

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