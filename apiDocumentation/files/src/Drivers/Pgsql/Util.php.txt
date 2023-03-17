<?php declare(strict_types=1);
/**
 * Query
 *
 * SQL Query Builder / Database Abstraction Layer
 *
 * PHP version 8.1
 *
 * @package     Query
 * @author      Timothy J. Warren <tim@timshome.page>
 * @copyright   2012 - 2023 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link        https://git.timshomepage.net/aviat/Query
 * @version     4.1.0
 */

namespace Query\Drivers\Pgsql;

use PDO;
use Query\Drivers\AbstractUtil;

/**
 * Postgres-specific backup, import and creation methods
 */
class Util extends AbstractUtil
{
	/**
	 * Create an SQL backup file for the current database's structure
	 */
	public function backupStructure(): string
	{
		// @TODO Implement Backup function
		return '';
	}

	/**
	 * Create an SQL backup file for the current database's data
	 */
	public function backupData(array $exclude=[]): string
	{
		$tables = $this->getDriver()->getTables();

		// Filter out the tables you don't want
		if ( ! empty($exclude))
		{
			$tables = array_diff($tables, $exclude);
		}

		$outputSql = '';

		// Get the data for each object
		foreach ($tables as $t)
		{
			$sql = 'SELECT * FROM "' . trim((string) $t) . '"';
			$res = $this->getDriver()->query($sql);
			$objRes = $res->fetchAll(PDO::FETCH_ASSOC);

			// Don't add to the file if the table is empty
			if ((is_countable($objRes) ? count($objRes) : 0) < 1)
			{
				continue;
			}

			$res = NULL;

			// Nab the column names by getting the keys of the first row
			$columns = @array_keys($objRes[0]);

			$insertRows = [];

			// Create the insert statements
			foreach ($objRes as $row)
			{
				$row = array_values($row);

				// Quote values as needed by type
				$row = array_map([$this->getDriver(), 'quote'], $row);
				$row = array_map('trim', $row);

				$rowString = 'INSERT INTO "' . trim((string) $t) . '" ("' . implode('","', $columns) . '") VALUES (' . implode(',', $row) . ');';

				$row = NULL;

				$insertRows[] = $rowString;
			}

			$objRes = NULL;

			$outputSql .= "\n\n" . implode("\n", $insertRows) . "\n";
		}

		return $outputSql;
	}
}
