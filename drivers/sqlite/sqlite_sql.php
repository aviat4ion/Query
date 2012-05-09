<?php
/**
 * Query
 *
 * Free Query Builder / Database Abstraction Layer
 *
 * @package		Query
 * @author		Timothy J. Warren
 * @copyright	Copyright (c) 2012
 * @link 		https://github.com/aviat4ion/Query
 * @license		http://philsturgeon.co.uk/code/dbad-license
 */

// --------------------------------------------------------------------------

/**
 * SQLite Specific SQL
 *
 * @package Query
 * @subpackage Drivers
 */
class SQLite_SQL extends DB_SQL {

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
	 * @return FALSE
	 */
	public function db_list()
	{
		return FALSE;
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
		return FALSE;
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
	 * @return FALSE
	 */
	public function trigger_list()
	{
		return FALSE;
	}

	// --------------------------------------------------------------------------

	/**
	 * Return sql to list functions
	 *
	 * @return FALSE
	 */
	public function function_list()
	{
		return FALSE;
	}

	// --------------------------------------------------------------------------

	/**
	 * Return sql to list stored procedures
	 *
	 * @return FALSE
	 */
	public function procedure_list()
	{
		return FALSE;
	}

	// --------------------------------------------------------------------------

	/**
	 * Return sql to list sequences
	 *
	 * @return FALSE
	 */
	public function sequence_list()
	{
		return FALSE;
	}
	
	// --------------------------------------------------------------------------
	
	/**
	 * SQL to show list of field types
	 *
	 * @return array
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