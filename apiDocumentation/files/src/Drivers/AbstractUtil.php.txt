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

namespace Query\Drivers;

use function arrayZipper;

/**
 * Abstract class defining database / table creation methods
 */
abstract class AbstractUtil
{
	/**
	 * Save a reference to the connection object for later use
	 */
	public function __construct(private readonly DriverInterface $connection)
	{
	}

	/**
	 * Get the driver object for the current connection
	 */
	public function getDriver(): DriverInterface
	{
		return $this->connection;
	}

	/**
	 * Convenience public function to generate sql for creating a db table
	 */
	public function createTable(string $name, array $fields, array $constraints=[], bool $ifNotExists=TRUE): string
	{
		$existsStr = $ifNotExists ? ' IF NOT EXISTS ' : ' ';

		// Reorganize into an array indexed with column information
		// Eg $columnArray[$colname] = [
		// 		'type' => ...,
		// 		'constraint' => ...,
		// 		'index' => ...,
		// ]
		$columnArray = arrayZipper([
			'type' => $fields,
			'constraint' => $constraints,
		]);

		// Join column definitions together
		$columns = [];

		foreach ($columnArray as $n => $props)
		{
			$str = $this->getDriver()->quoteIdent($n);
			$str .= isset($props['type']) ? " {$props['type']}" : '';
			$str .= isset($props['constraint']) ? " {$props['constraint']}" : '';

			$columns[] = $str;
		}

		// Generate the sql for the creation of the table
		$sql = 'CREATE TABLE' . $existsStr . $this->getDriver()->quoteTable($name) . ' (';
		$sql .= implode(', ', $columns);
		$sql .= ')';

		return $sql;
	}

	/**
	 * Drop the selected table
	 */
	public function deleteTable(string $name): string
	{
		return 'DROP TABLE IF EXISTS ' . $this->getDriver()->quoteTable($name);
	}

	// --------------------------------------------------------------------------
	// ! Abstract Methods
	// --------------------------------------------------------------------------
	/**
	 * Return an SQL file with the database table structure
	 */
	abstract public function backupStructure(): string;

	/**
	 * Return an SQL file with the database data as insert statements
	 */
	abstract public function backupData(): string;
}
