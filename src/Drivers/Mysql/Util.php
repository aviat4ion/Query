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
 * @version     4.0.0
 */

namespace Query\Drivers\Mysql;

use PDO;
use Query\Drivers\AbstractUtil;

/**
 * MySQL-specific backup, import and creation methods
 */
class Util extends AbstractUtil
{
	/**
	 * Create an SQL backup file for the current database's structure
	 */
	public function backupStructure(): string
	{
		$string = [];

		// Get databases
		$driver = $this->getDriver();
		$dbs = $driver->getDbs();

		foreach ($dbs as &$d)
		{
			// Skip built-in dbs
			// @codeCoverageIgnoreStart
			if ($d === 'mysql')
			{
				continue;
			}
			// @codeCoverageIgnoreEnd

			// Get the list of tables
			$tables = $driver->driverQuery("SHOW TABLES FROM `{$d}`", TRUE);

			foreach ($tables as $table)
			{
				$array = $driver->driverQuery("SHOW CREATE TABLE `{$d}`.`{$table}`", FALSE);
				$row = current($array);

				if ( ! isset($row['Create Table']))
				{
					continue;
				}

				$string[] = $row['Create Table'];
			}
		}

		return implode("\n\n", $string);
	}

	/**
	 * Create an SQL backup file for the current database's data
	 */
	public function backupData(array $exclude=[]): string
	{
		$driver = $this->getDriver();
		$tables = $driver->getTables();

		// Filter out the tables you don't want
		if ( ! empty($exclude))
		{
			$tables = array_diff($tables, $exclude);
		}

		$outputSql = '';

		// Select the rows from each Table
		foreach ($tables as $t)
		{
			$sql = "SELECT * FROM `{$t}`";
			$res = $driver->query($sql);
			$rows = $res->fetchAll(PDO::FETCH_ASSOC);

			// Skip empty tables
			if ((is_countable($rows) ? count($rows) : 0) < 1)
			{
				continue;
			}

			// Nab the column names by getting the keys of the first row
			$columns = @array_keys($rows[0]);

			$insertRows = [];

			// Create the insert statements
			foreach ($rows as $row)
			{
				$row = array_values($row);

				// Quote strings
				$row = array_map(static fn ($r) => is_string($r) ? $driver->quote($r) : $r, $row);
				$row = array_map('trim', $row);

				$rowString = 'INSERT INTO `' . trim($t) . '` (`' . implode('`,`', $columns) . '`) VALUES (' . implode(',', $row) . ');';

				$row = NULL;

				$insertRows[] = $rowString;
			}

			$outputSql .= "\n\n" . implode("\n", $insertRows) . "\n";
		}

		return $outputSql;
	}
}
