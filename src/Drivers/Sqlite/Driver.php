<?php declare(strict_types=1);
/**
 * Query
 *
 * SQL Query Builder / Database Abstraction Layer
 *
 * PHP version 8.1
 *
 * @package     Query
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2012 - 2022 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link        https://git.timshomepage.net/aviat/Query
 * @version     4.0.0
 */
namespace Query\Drivers\Sqlite;

use function is_array;

use InvalidArgumentException;
use PDO;
use Query\Drivers\AbstractDriver;

/**
 * SQLite specific class
 */
class Driver extends AbstractDriver {
	/**
	 * SQLite has a truncate optimization,
	 * but no support for the actual keyword
	 */
	protected bool $hasTruncate = FALSE;

	/**
	 * Open SQLite Database
	 */
	public function __construct(string $dsn, string $user=NULL, string $pass=NULL, array $driverOptions=[])
	{
		if ( ! str_contains($dsn, 'sqlite:'))
		{
			$dsn = "sqlite:{$dsn}";
		}

		parent::__construct($dsn, $user, $pass);
	}

	/**
	 * Return list of dbs for the current connection, if possible. Meaningless for SQLite.
	 */
	public function getDbs(): ?array
	{
		return NULL;
	}

	/**
	 * List tables for the current database
	 */
	public function getTables(): array
	{
		$sql = $this->getSql()->tableList();
		$res = $this->query($sql);
		return dbFilter($res->fetchAll(PDO::FETCH_ASSOC), 'name');
	}

	/**
	 * Retrieve foreign keys for the table
	 */
	public function getFks(string $table): array
	{
		$returnRows = [];

		foreach(parent::getFks($table) as $row)
		{
			$returnRows[] = [
				'child_column' => $row['from'],
				'parent_table' => $row['table'],
				'parent_column' => $row['to'],
				'update' => $row['on_update'],
				'delete' => $row['on_delete']
			];
		}

		return $returnRows;
	}

	/**
	 * Create sql for batch insert
	 *
	 * @codeCoverageIgnore
	 * @return array[]|string[]|null[]
	 */
	public function insertBatch(string $table, array $data=[]): array
	{
		// If greater than version 3.7.11, supports the same syntax as
		// MySQL and Postgres
		if (version_compare($this->getVersion(), '3.7.11', '>='))
		{
			return parent::insertBatch($table, $data);
		}

		// --------------------------------------------------------------------------
		// Otherwise, do a union query as an analogue to a 'proper' batch insert
		// --------------------------------------------------------------------------

		// Each member of the data array needs to be an array
		if ( ! is_array(current($data)))
		{
			throw new InvalidArgumentException('$data must be an array of arrays');
		}

		// Start the block of sql statements
		$table = $this->quoteTable($table);
		$sql = "INSERT INTO {$table} \n";

		// Create a key-value mapping for each field
		$first = array_shift($data);
		$cols = [];
		foreach($first as $colName => $datum)
		{
			$cols[] = $this->_quote($datum) . ' AS ' . $this->quoteIdent($colName);
		}
		$sql .= 'SELECT ' . implode(', ', $cols) . "\n";

		foreach($data as $union)
		{
			$vals = array_map([$this, 'quote'], $union);
			$sql .= 'UNION SELECT ' . implode(',', $vals) . "\n";
		}

		return [$sql, NULL];
	}

	/**
	 * Generate the returning clause for the current database
	 */
	public function returning(string $query, string $select): string
	{
		// Return the same query, as the returning clause is not supported
		return $query;
	}
}