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
	 * @return NULL
	 */
	public function db_list()
	{
		return NULL;
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
			SELECT "name"
			FROM "sqlite_master"
			WHERE "type"='table'
			ORDER BY "name" DESC
SQL;
	}

	// --------------------------------------------------------------------------

	/**
	 * Overridden in SQLite class
	 *
	 * @return string
	 */
	public function system_table_list()
	{
		return NULL;
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
		return NULL;
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

}
//End of sqlite_sql.php