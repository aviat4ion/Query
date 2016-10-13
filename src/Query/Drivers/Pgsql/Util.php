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



namespace Query\Drivers\Pgsql;

use Query\Drivers\AbstractUtil;

/**
 * Posgres-specific backup, import and creation methods
 *
 * @package Query
 * @subpackage Drivers
 */
class Util extends AbstractUtil {

	/**
	 * Create an SQL backup file for the current database's structure
	 *
	 * @return string
	 */
	public function backup_structure()
	{
		// @TODO Implement Backup function
		return '';
	}

	// --------------------------------------------------------------------------

	/**
	 * Create an SQL backup file for the current database's data
	 *
	 * @param array $exclude
	 * @return string
	 */
	public function backup_data($exclude=[])
	{
		$tables = $this->get_driver()->get_tables();

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

			$res = NULL;

			// Nab the column names by getting the keys of the first row
			$columns = @array_keys($obj_res[0]);

			$insert_rows = [];

			// Create the insert statements
			foreach($obj_res as $row)
			{
				$row = array_values($row);

				// Quote values as needed by type
				$row = array_map([$this->get_driver(), 'quote'], $row);
				$row = array_map('trim', $row);


				$row_string = 'INSERT INTO "'.trim($t).'" ("'.implode('","', $columns).'") VALUES ('.implode(',', $row).');';

				$row = NULL;

				$insert_rows[] = $row_string;
			}

			$obj_res = NULL;

			$output_sql .= "\n\n".implode("\n", $insert_rows)."\n";
		}

		return $output_sql;
	}
}
// End of pgsql_util.php