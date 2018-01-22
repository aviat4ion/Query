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
namespace Query\Drivers;

/**
 * Abstract class defining database / table creation methods
 *
 * @method string quoteIdent(string $sql)
 * @method string quoteTable(string $sql)
 */
abstract class AbstractUtil {

	/**
	 * Reference to the current connection object
	 * @var DriverInterface
	 */
	private $connection;

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
	public function getDriver()
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
	public function createTable($name, $fields, array $constraints=[], $ifNotExists=TRUE)
	{
		$existsStr = $ifNotExists ? ' IF NOT EXISTS ' : ' ';

		// Reorganize into an array indexed with column information
		// Eg $columnArray[$colname] = array(
		// 		'type' => ...,
		// 		'constraint' => ...,
		// 		'index' => ...,
		// )
		$columnArray = \array_zipper([
			'type' => $fields,
			'constraint' => $constraints
		]);

		// Join column definitions together
		$columns = [];
		foreach($columnArray as $n => $props)
		{
			$str = $this->getDriver()->quoteIdent($n);
			$str .= isset($props['type']) ? " {$props['type']}" : "";
			$str .= isset($props['constraint']) ? " {$props['constraint']}" : "";

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
	abstract public function backupStructure();

	/**
	 * Return an SQL file with the database data as insert statements
	 *
	 * @abstract
	 * @return string
	 */
	abstract public function backupData();

}