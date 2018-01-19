<?php declare(strict_types=1);
/**
 * Query
 *
 * SQL Query Builder / Database Abstraction Layer
 *
 * PHP version 7.1
 *
 * @package     Query
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2012 - 2018 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link        https://git.timshomepage.net/aviat4ion/Query
 */
namespace Query\Drivers\Firebird;

use PDO;
use Query\Drivers\AbstractUtil;

/**
 * Firebird-specific backup, import and creation methods
 */
class Util extends AbstractUtil {

	/**
	 * Convenience public function to generate sql for creating a db table
	 *
	 * @param string $name
	 * @param array $fields
	 * @param array $constraints
	 * @param bool $ifNotExists
	 * @return string
	 */
	public function createTable($name, $fields, array $constraints=[], $ifNotExists=FALSE)
	{
		return parent::createTable($name, $fields, $constraints, FALSE);
	}

	/**
	 * Drop the selected table
	 *
	 * @param string $name
	 * @return string
	 */
	public function deleteTable($name)
	{
		return 'DROP TABLE '.$this->getDriver()->quoteTable($name);
	}

	/**
	 * Create an SQL backup file for the current database's structure
	 *
	 * @return string
	 */
	public function backupStructure(/* @param string $dbPath, @param string $newFile */)
	{
		list($dbPath, $newFile) = func_get_args();
		return ibase_backup($this->getDriver()->getService(), $dbPath, $newFile, \IBASE_BKP_METADATA_ONLY);
	}

	/**
	 * Create an SQL backup file for the current database's data
	 *
	 * @param array $exclude
	 * @param bool $systemTables
	 * @return string
	 */
	public function backupData($exclude=[], $systemTables=FALSE)
	{
		// Determine which tables to use
		$tables = $this->getDriver()->getTables();
		if($systemTables == TRUE)
		{
			$tables = array_merge($tables, $this->getDriver()->getSystemTables());
		}

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

			// Nab the column names by getting the keys of the first row
			$columns = @array_keys($objRes[0]);

			$insertRows = [];

			// Create the insert statements
			foreach($objRes as $row)
			{
				$row = array_values($row);

				// Quote values as needed by type
				if(stripos($t, 'RDB$') === FALSE)
				{
					$row = array_map([$this->getDriver(), 'quote'], $row);
					$row = array_map('trim', $row);
				}

				$rowString = 'INSERT INTO "'.trim($t).'" ("'.implode('","', $columns).'") VALUES ('.implode(',', $row).');';

				$row = NULL;

				$insertRows[] = $rowString;
			}

			$outputSql .= "\n\nSET TRANSACTION;\n".implode("\n", $insertRows)."\nCOMMIT;";
		}

		return $outputSql;
	}
}