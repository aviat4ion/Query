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
namespace Query\Drivers\Sqlite;

use Query\Drivers\AbstractSQL;

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
	 * Returns sql to list other databases
	 *
	 * @return string
	 */
	public function dbList(): string
	{
		return 'PRAGMA database_list';
	}

	/**
	 * Returns sql to list tables
	 *
	 * @return string
	 */
	public function tableList(): string
	{
		return <<<SQL
			SELECT DISTINCT "name"
			FROM "sqlite_master"
			WHERE "type"='table'
			AND "name" NOT LIKE 'sqlite_%'
			ORDER BY "name" DESC
SQL;
	}

	/**
	 * List the system tables
	 *
	 * @return string[]
	 */
	public function systemTableList(): array
	{
		return ['sqlite_master', 'sqlite_temp_master', 'sqlite_sequence'];
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
		return 'SELECT "name" FROM "sqlite_master" WHERE "type"=\'trigger\'';
	}

	/**
	 * Return sql to list functions
	 *
	 * @return string
	 */
	public function functionList(): ?string
	{
		return NULL;
	}

	/**
	 * Return sql to list stored procedures
	 *
	 * @return string
	 */
	public function procedureList(): ?string
	{
		return NULL;
	}

	/**
	 * Return sql to list sequences
	 *
	 * @return string
	 */
	public function sequenceList(): ?string
	{
		return NULL;
	}

	/**
	 * SQL to show list of field types
	 *
	 * @return string[]
	 */
	public function typeList(): array
	{
		return ['INTEGER', 'REAL', 'TEXT', 'BLOB'];
	}

	/**
	 * SQL to show information about columns in a table
	 *
	 * @param string $table
	 * @return string
	 */
	public function columnList(string $table): string
	{
		return 'PRAGMA table_info("' . $table . '")';
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
		return 'PRAGMA foreign_key_list("' . $table . '")';
	}


	/**
	 * Get the list of indexes for the current table
	 *
	 * @param string $table
	 * @return string
	 */
	public function indexList(string $table): string
	{
		return 'PRAGMA index_list("' . $table . '")';
	}
}