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
namespace Query\Drivers\Pgsql;

use PDO;
use Query\Drivers\AbstractUtil;

/**
 * Postgres-specific backup, import and creation methods
 */
class Util extends AbstractUtil {

	/**
	 * Create an SQL backup file for the current database's structure
	 *
	 * @return string
	 */
	public function backupStructure(): string
	{
		// @TODO Implement Backup function
		return '';
	}

	/**
	 * Create an SQL backup file for the current database's data
	 *
	 * @param array $exclude
	 * @return string
	 */
	public function backupData(array $exclude=[]): string
	{
		$tables = $this->getDriver()->getTables();

		// Filter out the tables you don't want
		if( ! empty($exclude))
		{
			$tables = array_diff($tables, $exclude);
		}

		$outputSql = '';

		// Get the data for each object
		foreach($tables as $t)
		{
			$sql = 'SELECT * FROM "'.trim($t).'"';
			$res = $this->getDriver()->query($sql);
			$objRes = $res->fetchAll(PDO::FETCH_ASSOC);

			// Don't add to the file if the table is empty
			if (count($objRes) < 1)
			{
				continue;
			}

			$res = NULL;

			// Nab the column names by getting the keys of the first row
			$columns = @array_keys($objRes[0]);

			$insertRows = [];

			// Create the insert statements
			foreach($objRes as $row)
			{
				$row = array_values($row);

				// Quote values as needed by type
				$row = array_map([$this->getDriver(), 'quote'], $row);
				$row = array_map('trim', $row);


				$rowString = 'INSERT INTO "'.trim($t).'" ("'.implode('","', $columns).'") VALUES ('.implode(',', $row).');';

				$row = NULL;

				$insertRows[] = $rowString;
			}

			$objRes = NULL;

			$outputSql .= "\n\n".implode("\n", $insertRows)."\n";
		}

		return $outputSql;
	}
}