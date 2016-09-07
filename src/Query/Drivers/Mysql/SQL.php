<?php
/**
 * Query
 *
 * SQL Query Builder / Database Abstraction Layer
 *
 * PHP version 5.4
 *
 * @package     Query
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2012 - 2015 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link        https://git.timshomepage.net/aviat4ion/Query
 */


// --------------------------------------------------------------------------

namespace Query\Drivers\Mysql;

/**
 * MySQL specifc SQL
 *
 * @package Query
 * @subpackage Drivers
 */
class SQL extends \Query\AbstractSQL {

	/**
	 * Limit clause
	 *
	 * @param string $sql
	 * @param int $limit
	 * @param int $offset
	 * @return string
	 */
	public function limit($sql, $limit, $offset=FALSE)
	{
		if ( ! is_numeric($offset))
		{
			return $sql." LIMIT {$limit}";
		}

		return $sql." LIMIT {$offset}, {$limit}";
	}

	// --------------------------------------------------------------------------

	/**
	 * Get the query plan for the sql query
	 *
	 * @param string $sql
	 * @return string
	 */
	public function explain($sql)
	{
		return "EXPLAIN EXTENDED {$sql}";
	}

	// --------------------------------------------------------------------------

	/**
	 * Random ordering keyword
	 *
	 * @return string
	 */
	public function random()
	{
		return ' RAND() DESC';
	}

	// --------------------------------------------------------------------------

	/**
	 * Returns sql to list other databases
	 *
	 * @return string
	 */
	public function db_list()
	{
		return "SHOW DATABASES WHERE `Database` NOT IN ('information_schema','mysql')";
	}

	// --------------------------------------------------------------------------

	/**
	 * Returns sql to list tables
	 *
	 * @param string $database
	 * @return string
	 */
	public function table_list($database='')
	{
		if ( ! empty($database))
		{
			return "SHOW TABLES FROM `{$database}`";
		}

		return 'SHOW TABLES';
	}

	// --------------------------------------------------------------------------

	/**
	 * Overridden in MySQL class
	 *
	 * @return string
	 */
	public function system_table_list()
	{
		return 'SELECT `TABLE_NAME` FROM `information_schema`.`TABLES`
			WHERE `TABLE_SCHEMA`=\'information_schema\'';
	}

	// --------------------------------------------------------------------------

	/**
	 * Returns sql to list views
	 *
	 * @return string
	 */
	public function view_list()
	{
		return 'SELECT `table_name` FROM `information_schema`.`views`';
	}

	// --------------------------------------------------------------------------

	/**
	 * Returns sql to list triggers
	 *
	 * @return string
	 */
	public function trigger_list()
	{
		return 'SHOW TRIGGERS';
	}

	// --------------------------------------------------------------------------

	/**
	 * Return sql to list functions
	 *
	 * @return string
	 */
	public function function_list()
	{
		return 'SHOW FUNCTION STATUS';
	}

	// --------------------------------------------------------------------------

	/**
	 * Return sql to list stored procedures
	 *
	 * @return string
	 */
	public function procedure_list()
	{
		return 'SHOW PROCEDURE STATUS';
	}

	// --------------------------------------------------------------------------

	/**
	 * Return sql to list sequences
	 *
	 * @return NULL
	 */
	public function sequence_list()
	{
		return NULL;
	}

	// --------------------------------------------------------------------------

	/**
	 * SQL to show list of field types
	 *
	 * @return string
	 */
	public function type_list()
	{
		return "SELECT DISTINCT `DATA_TYPE` FROM `information_schema`.`COLUMNS`";
	}

	// --------------------------------------------------------------------------

	/**
	 * SQL to show infromation about columns in a table
	 *
	 * @param string $table
	 * @return string
	 */
	public function column_list($table)
	{
		return "SHOW FULL COLUMNS FROM {$table}";
	}

	// --------------------------------------------------------------------------

	/**
	 * Get the list of foreign keys for the current
	 * table
	 *
	 * @param string $table
	 * @return string
	 */
	public function fk_list($table)
	{
		return <<<SQL
			SELECT DISTINCT `kcu`.`COLUMN_NAME` as `child_column`,
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

	// --------------------------------------------------------------------------

	/**
	 * Get the list of indexes for the current table
	 *
	 * @param string $table
	 * @return array
	 */
	public function index_list($table)
	{
		return "SHOW INDEX IN {$table}";
	}
}
//End of mysql_sql.php