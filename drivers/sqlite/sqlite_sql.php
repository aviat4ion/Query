<?php
/**
 * Query
 *
 * Free Query Builder / Database Abstraction Layer
 *
 * @package		Query
 * @author		Timothy J. Warren
 * @copyright	Copyright (c) 2012 - 2014
 * @link 		https://github.com/aviat4ion/Query
 * @license		http://philsturgeon.co.uk/code/dbad-license
 */

// --------------------------------------------------------------------------

namespace Query\Driver;

/**
 * SQLite Specific SQL
 *
 * @package Query
 * @subpackage Drivers
 */
class SQLite_SQL extends Abstract_SQL {

	/**
	 * Get the query plan for the sql query
	 *
	 * @param string $sql
	 * @return string
	 */
	public function explain($sql)
	{
		return "EXPLAIN QUERY PLAN {$sql}";
	}

	// --------------------------------------------------------------------------

	/**
	 * Random ordering keyword
	 *
	 * @return string
	 */
	public function random()
	{
		return ' RANDOM()';
	}

	// --------------------------------------------------------------------------

	/**
	 * Returns sql to list other databases
	 *
	 * @return string
	 */
	public function db_list()
	{
		return 'PRAGMA database_list';
	}

	// --------------------------------------------------------------------------

	/**
	 * Returns sql to list tables
	 *
	 * @return string
	 */
	public function table_list()
	{
		return <<<SQL
			SELECT DISTINCT "name"
			FROM "sqlite_master"
			WHERE "type"='table'
			AND "name" NOT LIKE 'sqlite_%'
			ORDER BY "name" DESC
SQL;
	}

	// --------------------------------------------------------------------------

	/**
	 * List the system tables
	 *
	 * @return array
	 */
	public function system_table_list()
	{
		return array('sqlite_master', 'sqlite_temp_master', 'sqlite_sequence');
	}

	// --------------------------------------------------------------------------

	/**
	 * Returns sql to list views
	 *
	 * @return string
	 */
	public function view_list()
	{
		return <<<SQL
			SELECT "name" FROM "sqlite_master" WHERE "type" = 'view'
SQL;
	}

	// --------------------------------------------------------------------------

	/**
	 * Returns sql to list triggers
	 *
	 * @return NULL
	 */
	public function trigger_list()
	{
		return 'SELECT "name" FROM "sqlite_master" WHERE "type"=\'trigger\'';
	}

	// --------------------------------------------------------------------------

	/**
	 * Return sql to list functions
	 *
	 * @return NULL
	 */
	public function function_list()
	{
		return NULL;
	}

	// --------------------------------------------------------------------------

	/**
	 * Return sql to list stored procedures
	 *
	 * @return NULL
	 */
	public function procedure_list()
	{
		return NULL;
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
	 * @return string[]
	 */
	public function type_list()
	{
		return array('INTEGER', 'REAL', 'TEXT', 'BLOB');
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
		return 'PRAGMA table_info("'.$table.'")';
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
		return 'PRAGMA foreign_key_list("' . $table . '")';
	}

	// --------------------------------------------------------------------------

	/**
	 * Get the list of indexes for the current table
	 *
	 * @param string $table
	 * @return string
	 */
	public function index_list($table)
	{
		return 'PRAGMA index_list("' . $table . '")';
	}

}
//End of sqlite_sql.php