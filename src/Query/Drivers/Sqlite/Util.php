<?php declare(strict_types=1);
/**
 * Query
 *
 * SQL Query Builder / Database Abstraction Layer
 *
 * PHP version 7
 *
 * @package     Query
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2012 - 2016 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link        https://git.timshomepage.net/aviat4ion/Query
 */



namespace Query\Drivers\Sqlite;

use Query\Drivers\AbstractUtil;

/**
 * SQLite-specific backup, import and creation methods
 *
 * @package Query
 * @subpackage Drivers
 * @method mixed query(string $sql)
 * @method string quote(string $str)
 */
class Util extends AbstractUtil {

	/**
	 * Create an SQL backup file for the current database's data
	 *
	 * @param array $excluded
	 * @return string
	 */
	public function backup_data($excluded=[])
	{
		// Get a list of all the objects
		$sql = 'SELECT DISTINCT "name"
				FROM "sqlite_master"
				WHERE "type"=\'table\'';

		if( ! empty($excluded))
		{
			$sql .= " AND \"name\" NOT IN('".implode("','", $excluded)."')";
		}

		$res = $this->get_driver()->query($sql);
		$result = $res->fetchAll(\PDO::FETCH_ASSOC);

		unset($res);

		$output_sql = '';

		// Get the data for each object
		foreach($result as $r)
		{
			$sql = 'SELECT * FROM "'.$r['name'].'"';
			$res = $this->get_driver()->query($sql);
			$obj_res = $res->fetchAll(\PDO::FETCH_ASSOC);

			unset($res);

			// If the row is empty, continue;
			if (empty($obj_res))
			{
				continue;
			}

			// Nab the column names by getting the keys of the first row
			$columns = array_keys(current($obj_res));

			$insert_rows = [];

			// Create the insert statements
			foreach($obj_res as $row)
			{
				$row = array_values($row);

				// Quote values as needed by type
				for($i=0, $icount=count($row); $i<$icount; $i++)
				{
					$row[$i] = (is_numeric($row[$i])) ? $row[$i] : $this->get_driver()->quote($row[$i]);
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
		$res = $this->get_driver()->query($sql);
		$result = $res->fetchAll(\PDO::FETCH_ASSOC);

		$sql_array = [];

		foreach($result as $r)
		{
			$sql_array[] = $r['sql'];
		}

		return implode(";\n", $sql_array) . ";";
	}
}
// End of sqlite_util.php