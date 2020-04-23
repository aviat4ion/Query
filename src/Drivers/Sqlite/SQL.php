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
namespace Query\Drivers\Sqlite;

use Query\Drivers\AbstractSQL;
use Query\Exception\NotImplementedException;

/**
 * SQLite Specific SQL
 */
class SQL extends AbstractSQL {

	/**
	 * Get the query plan for the sql query
	 *
	 * @param string $sql
	 * @return string
	 */
	public function explain(string $sql): string
	{
		return "EXPLAIN QUERY PLAN {$sql}";
	}

	/**
	 * Random ordering keyword
	 *
	 * @return string
	 */
	public function random(): string
	{
		return ' RANDOM()';
	}

	/**
	 * Returns sql to list other databases. Meaningless for SQLite, as this
	 * just returns the database(s) that we are currently connected to.
	 *
	 * @return string
	 */
	public function dbList(): string
	{
		return '';
	}

	/**
	 * Returns sql to list tables
	 *
	 * @return string
	 */
	public function tableList(): string
	{
		return <<<SQL
            SELECT "name" FROM (
				SELECT * FROM "sqlite_master" UNION ALL
				SELECT * FROM "sqlite_temp_master"
			)
        	WHERE "type"='table'
        	AND "name" NOT LIKE "sqlite_%"
        	ORDER BY "name"
SQL;
	}

	/**
	 * List the system tables
	 *
	 * @return string[]
	 */
	public function systemTableList(): array
	{
		return [
			'sqlite_master',
			'sqlite_temp_master',
			'sqlite_sequence'
		];
	}

	/**
	 * Returns sql to list views
	 *
	 * @return string
	 */
	public function viewList(): string
	{
		return <<<SQL
			SELECT "name" FROM "sqlite_master" WHERE "type" = 'view'
SQL;
	}

	/**
	 * Returns sql to list triggers
	 *
	 * @return string
	 */
	public function triggerList(): string
	{
		return <<<SQL
			SELECT "name" FROM "sqlite_master" WHERE "type"='trigger'
SQL;
	}

	/**
	 * Return sql to list functions
	 *
	 * @throws NotImplementedException
	 * @return string
	 */
	public function functionList(): string
	{
		throw new NotImplementedException('Functionality does not exist in SQLite');
	}

	/**
	 * Return sql to list stored procedures
	 *
	 * @throws NotImplementedException
	 * @return string
	 */
	public function procedureList(): string
	{
		throw new NotImplementedException('Functionality does not exist in SQLite');
	}

	/**
	 * Return sql to list sequences
	 *
	 * @return string
	 */
	public function sequenceList(): string
	{
		return 'SELECT "name" FROM "sqlite_sequence"';
	}

	/**
	 * SQL to show list of field types
	 *
	 * @return string[]
	 */
	public function typeList(): array
	{
		return ['INTEGER', 'REAL', 'TEXT', 'BLOB', 'NULL'];
	}

	/**
	 * SQL to show information about columns in a table
	 *
	 * @param string $table
	 * @return string
	 */
	public function columnList(string $table): string
	{
		return <<<SQL
			PRAGMA table_info("$table")
SQL;
	}

	/**
	 * Get the list of foreign keys for the current
	 * table
	 *
	 * @param string $table
	 * @return string
	 */
	public function fkList(string $table): string
	{
		return <<<SQL
			PRAGMA foreign_key_list("$table")
SQL;
	}


	/**
	 * Get the list of indexes for the current table
	 *
	 * @param string $table
	 * @return string
	 */
	public function indexList(string $table): string
	{
		return <<<SQL
			PRAGMA index_list("$table")
SQL;
	}
}