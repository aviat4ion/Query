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

use Query\Drivers\AbstractSQL;

/**
 * MySQL specific SQL
 */
class SQL extends AbstractSQL {

	/**
	 * Limit clause
	 *
	 * @param string $sql
	 * @param int $limit
	 * @param int|boolean $offset
	 * @return string
	 */
	public function limit(string $sql, int $limit, ?int $offset=NULL): string
	{
		if ( ! is_numeric($offset))
		{
			return $sql." LIMIT {$limit}";
		}

		return $sql." LIMIT {$offset}, {$limit}";
	}

	/**
	 * Get the query plan for the sql query
	 *
	 * @param string $sql
	 * @return string
	 */
	public function explain(string $sql): string
	{
		return "EXPLAIN EXTENDED {$sql}";
	}

	/**
	 * Random ordering keyword
	 *
	 * @return string
	 */
	public function random(): string
	{
		return ' RAND() DESC';
	}

	/**
	 * Returns sql to list other databases
	 *
	 * @return string
	 */
	public function dbList(): string
	{
		return <<<SQL
			SHOW DATABASES WHERE `Database` NOT IN ('information_schema','mysql')
SQL;

	}

	/**
	 * Returns sql to list tables
	 *
	 * @param string $database
	 * @return string
	 */
	public function tableList($database=''): string
	{
		// @codeCoverageIgnoreStart
		if ( ! empty($database))
		{
			return "SHOW TABLES FROM `{$database}`";
		}
		// @codeCoverageIgnoreEnd

		return 'SHOW TABLES';
	}

	/**
	 * Overridden in MySQL class
	 *
	 * @return string
	 */
	public function systemTableList(): string
	{
		return <<<SQL
			SELECT `TABLE_NAME` FROM `information_schema`.`TABLES`
			WHERE `TABLE_SCHEMA`='information_schema'
SQL;
	}

	/**
	 * Returns sql to list views
	 *
	 * @return string
	 */
	public function viewList(): string
	{
		return 'SELECT `table_name` FROM `information_schema`.`views`';
	}

	/**
	 * Returns sql to list triggers
	 *
	 * @return string
	 */
	public function triggerList(): string
	{
		return 'SHOW TRIGGERS';
	}

	/**
	 * Return sql to list functions
	 *
	 * @return string
	 */
	public function functionList(): string
	{
		return 'SHOW FUNCTION STATUS';
	}

	/**
	 * Return sql to list stored procedures
	 *
	 * @return string
	 */
	public function procedureList(): string
	{
		return 'SHOW PROCEDURE STATUS';
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
	 * @return string
	 */
	public function typeList(): string
	{
		return 'SELECT DISTINCT `DATA_TYPE` FROM `information_schema`.`COLUMNS`';
	}

	/**
	 * SQL to show information about columns in a table
	 *
	 * @param string $table
	 * @return string
	 */
	public function columnList(string $table): string
	{
		return "SHOW FULL COLUMNS FROM {$table}";
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
			SELECT DISTINCT
				`kcu`.`COLUMN_NAME` as `child_column`,
				`kcu`.`REFERENCED_TABLE_NAME` as `parent_table`,
				`kcu`.`REFERENCED_COLUMN_NAME` as `parent_column`,
				`rc`.`UPDATE_RULE` AS `update`,
				`rc`.`DELETE_RULE` AS `delete`
			FROM `INFORMATION_SCHEMA`.`TABLE_CONSTRAINTS` `tc`
			INNER JOIN `INFORMATION_SCHEMA`.`KEY_COLUMN_USAGE` `kcu`
				ON `kcu`.`CONSTRAINT_NAME`=`tc`.`CONSTRAINT_NAME`
			INNER JOIN `INFORMATION_SCHEMA`.`REFERENTIAL_CONSTRAINTS` `rc`
				ON `rc`.`CONSTRAINT_NAME`=`tc`.`CONSTRAINT_NAME`
			WHERE `tc`.`CONSTRAINT_TYPE`='FOREIGN KEY'
			AND `tc`.`TABLE_NAME`='{$table}'
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
		return "SHOW INDEX IN {$table}";
	}
}