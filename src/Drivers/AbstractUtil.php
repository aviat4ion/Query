<?php declare(strict_types=1);
/**
 * Query
 *
 * SQL Query Builder / Database Abstraction Layer
 *
 * PHP version 7.2
 *
 * @package     Query
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2012 - 2020 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link        https://git.timshomepage.net/aviat/Query
 * @version     3.0.0
 */
namespace Query\Drivers;

/**
 * Abstract class defining database / table creation methods
 */
abstract class AbstractUtil {

	/**
	 * Reference to the current connection object
	 * @var DriverInterface
	 */
	private DriverInterface $connection;

	/**
	 * Save a reference to the connection object for later use
	 *
	 * @param DriverInterface $connection
	 */
	public function __construct(DriverInterface $connection)
	{
		$this->connection = $connection;
	}

	/**
	 * Get the driver object for the current connection
	 *
	 * @return DriverInterface
	 */
	public function getDriver(): DriverInterface
	{
		return $this->connection;
	}

	/**
	 * Convenience public function to generate sql for creating a db table
	 *
	 * @param string $name
	 * @param array $fields
	 * @param array $constraints
	 * @param bool $ifNotExists
	 * @return string
	 */
	public function createTable($name, $fields, array $constraints=[], $ifNotExists=TRUE): string
	{
		$existsStr = $ifNotExists ? ' IF NOT EXISTS ' : ' ';

		// Reorganize into an array indexed with column information
		// Eg $columnArray[$colname] = [
		// 		'type' => ...,
		// 		'constraint' => ...,
		// 		'index' => ...,
		// ]
		$columnArray = \arrayZipper([
			'type' => $fields,
			'constraint' => $constraints
		]);

		// Join column definitions together
		$columns = [];
		foreach($columnArray as $n => $props)
		{
			$str = $this->getDriver()->quoteIdent($n);
			$str .= isset($props['type']) ? " {$props['type']}" : '';
			$str .= isset($props['constraint']) ? " {$props['constraint']}" : '';

			$columns[] = $str;
		}

		// Generate the sql for the creation of the table
		$sql = 'CREATE TABLE'.$existsStr.$this->getDriver()->quoteTable($name).' (';
		$sql .= implode(', ', $columns);
		$sql .= ')';

		return $sql;
	}

	/**
	 * Drop the selected table
	 *
	 * @param string $name
	 * @return string
	 */
	public function deleteTable($name): string
	{
		return 'DROP TABLE IF EXISTS '.$this->getDriver()->quoteTable($name);
	}

	// --------------------------------------------------------------------------
	// ! Abstract Methods
	// --------------------------------------------------------------------------

	/**
	 * Return an SQL file with the database table structure
	 *
	 * @abstract
	 * @return string
	 */
	abstract public function backupStructure(): string;

	/**
	 * Return an SQL file with the database data as insert statements
	 *
	 * @abstract
	 * @return string
	 */
	abstract public function backupData(): string;

}