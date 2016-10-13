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


namespace Query\Drivers\Sqlite;

use PDO;
use PDOStatement;
use Query\Drivers\{AbstractDriver, DriverInterface};

/**
 * SQLite specific class
 *
 * @package Query
 * @subpackage Drivers
 */
class Driver extends AbstractDriver implements DriverInterface {

	/**
	 * Reference to the last executed sql query
	 *
	 * @var PDOStatement
	 */
	protected $statement;

	/**
	 * SQLite has a truncate optimization,
	 * but no support for the actual keyword
	 * @var boolean
	 */
	protected $has_truncate = FALSE;

	/**
	 * Open SQLite Database
	 *
	 * @param string $dsn
	 * @param string $user
	 * @param string $pass
	 * @param array $driver_options
	 */
	public function __construct($dsn, $user=NULL, $pass=NULL, array $driver_options=[])
	{
		if (strpos($dsn, 'sqlite:') === FALSE)
		{
			$dsn = "sqlite:{$dsn}";
		}

		parent::__construct($dsn, $user, $pass);
	}

	/**
	 * List tables for the current database
	 *
	 * @return mixed
	 */
	public function get_tables()
	{
		$sql = $this->sql->table_list();
		$res = $this->query($sql);
		return db_filter($res->fetchAll(PDO::FETCH_ASSOC), 'name');
	}

	/**
	 * Retrieve foreign keys for the table
	 *
	 * @param string $table
	 * @return array
	 */
	public function get_fks($table)
	{
		$return_rows = [];

		foreach(parent::get_fks($table) as $row)
		{
			$return_rows[] = [
				'child_column' => $row['from'],
				'parent_table' => $row['table'],
				'parent_column' => $row['to'],
				'update' => $row['on_update'],
				'delete' => $row['on_delete']
			];
		}

		return $return_rows;
	}

	/**
	 * Create sql for batch insert
	 *
	 * @codeCoverageIgnore
	 * @param string $table
	 * @param array $data
	 * @return string
	 */
	public function insert_batch($table, $data=[])
	{
		// If greater than version 3.7.11, supports the same syntax as
		// MySQL and Postgres
		if (version_compare($this->getAttribute(PDO::ATTR_SERVER_VERSION), '3.7.11', '>='))
		{
			return parent::insert_batch($table, $data);
		}

		// --------------------------------------------------------------------------
		// Otherwise, do a union query as an analogue to a 'proper' batch insert
		// --------------------------------------------------------------------------

		// Each member of the data array needs to be an array
		if ( ! is_array(current($data)))
		{
			return NULL;
		}

		// Start the block of sql statements
		$table = $this->quote_table($table);
		$sql = "INSERT INTO {$table} \n";

		// Create a key-value mapping for each field
		$first = array_shift($data);
		$cols = [];
		foreach($first as $colname => $datum)
		{
			$cols[] = $this->_quote($datum) . ' AS ' . $this->quote_ident($colname);
		}
		$sql .= "SELECT " . implode(', ', $cols) . "\n";

		foreach($data as $union)
		{
			$vals = array_map([$this, 'quote'], $union);
			$sql .= "UNION SELECT " . implode(',', $vals) . "\n";
		}

		return [$sql, NULL];
	}
}
//End of sqlite_driver.php