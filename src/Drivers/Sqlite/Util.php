<?php declare(strict_types=1);
/**
 * Query
 *
 * SQL Query Builder / Database Abstraction Layer
 *
 * PHP version 7.4
 *
 * @package     Query
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2012 - 2020 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link        https://git.timshomepage.net/aviat/Query
 * @version     3.0.0
 */
namespace Query\Drivers\Sqlite;

use PDO;
use Query\Drivers\AbstractUtil;

/**
 * SQLite-specific backup, import and creation methods
 */
class Util extends AbstractUtil {

	/**
	 * Create an SQL backup file for the current database's data
	 */
	public function backupData(array $excluded=[]): string
	{
		// Get a list of all the objects
		$sql = 'SELECT DISTINCT "name"
				FROM "sqlite_master"
				WHERE "type"=\'table\'';

		if( ! empty($excluded))
		{
			$sql .= " AND \"name\" NOT IN('".implode("','", $excluded)."')";
		}

		$res = $this->getDriver()->query($sql);
		$result = $res->fetchAll(PDO::FETCH_ASSOC);

		unset($res);

		$outputSql = '';

		// Get the data for each object
		foreach($result as $r)
		{
			$sql = 'SELECT * FROM "'.$r['name'].'"';
			$res = $this->getDriver()->query($sql);
			$objRes = $res->fetchAll(PDO::FETCH_ASSOC);

			unset($res);

			// If the row is empty, continue
			if (empty($objRes))
			{
				continue;
			}

			// Nab the column names by getting the keys of the first row
			$columns = array_keys(current($objRes));

			$insertRows = [];

			// Create the insert statements
			foreach($objRes as $row)
			{
				$row = array_values($row);

				// Quote values as needed by type
				foreach ($row as $i => $_)
				{
					$row[$i] = (is_numeric($row[$i]))
						? $row[$i]
						: $this->getDriver()->quote($row[$i]);
				}

				$rowString = 'INSERT INTO "'.$r['name'].'" ("'.implode('","', $columns).'") VALUES ('.implode(',', $row).');';

				unset($row);

				$insertRows[] = $rowString;
			}

			unset($objRes);

			$outputSql .= "\n\n".implode("\n", $insertRows);
		}

		return $outputSql;
	}

	/**
	 * Create an SQL backup file for the current database's structure
	 */
	public function backupStructure(): string
	{
		// Fairly easy for SQLite...just query the master table
		$sql = 'SELECT "sql" FROM "sqlite_master"';
		$res = $this->getDriver()->query($sql);
		$result = $res->fetchAll(PDO::FETCH_ASSOC);

		$sqlArray = [];

		foreach($result as $r)
		{
			$sqlArray[] = $r['sql'];
		}

		return implode(";\n", $sqlArray) . ';';
	}
}