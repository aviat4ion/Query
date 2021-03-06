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
namespace Query\Drivers\Mysql;

use PDO;
use Query\Drivers\AbstractUtil;

/**
 * MySQL-specific backup, import and creation methods
 */
class Util extends AbstractUtil {

	/**
	 * Create an SQL backup file for the current database's structure
	 *
	 * @return string
	 */
	public function backupStructure(): string
	{
		$string = [];

		// Get databases
		$driver = $this->getDriver();
		$dbs = $driver->getDbs();

		foreach($dbs as &$d)
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

			foreach($tables as $table)
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
	 *
	 * @param array $exclude
	 * @return string
	 */
	public function backupData(array $exclude=[]): string
	{
		$driver = $this->getDriver();
		$tables = $driver->getTables();

		// Filter out the tables you don't want
		if( ! empty($exclude))
		{
			$tables = array_diff($tables, $exclude);
		}

		$outputSql = '';

		// Select the rows from each Table
		foreach($tables as $t)
		{
			$sql = "SELECT * FROM `{$t}`";
			$res = $driver->query($sql);
			$rows = $res->fetchAll(PDO::FETCH_ASSOC);

			// Skip empty tables
			if (count($rows) < 1)
			{
				continue;
			}

			// Nab the column names by getting the keys of the first row
			$columns = @array_keys($rows[0]);

			$insertRows = [];

			// Create the insert statements
			foreach($rows as $row)
			{
				$row = array_values($row);

				// Workaround for Quercus
				foreach($row as &$r)
				{
					$r = $driver->quote($r);
				}
				unset($r);
				$row = array_map('trim', $row);

				$rowString = 'INSERT INTO `'.trim($t).'` (`'.implode('`,`', $columns).'`) VALUES ('.implode(',', $row).');';

				$row = NULL;

				$insertRows[] = $rowString;
			}

			$outputSql .= "\n\n".implode("\n", $insertRows)."\n";
		}

		return $outputSql;
	}
}