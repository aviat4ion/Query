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
 * Firebird Specific SQL
 *
 * @package Query
 * @subpackage Drivers
 */
class Firebird_SQL extends DB_SQL {

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
		// Keep the current sql string safe for a moment
		$orig_sql = $sql;

		$sql = 'FIRST '. (int) $limit;

		if ($offset > 0)
		{
			$sql .= ' SKIP '. (int) $offset;
		}

		$sql = preg_replace("`SELECT`i", "SELECT {$sql}", $orig_sql);

		return $sql;
	}

	// --------------------------------------------------------------------------

	/**
	 * Random ordering keyword
	 *
	 * @return string
	 */
	public function random()
	{
		return FALSE;
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
			SELECT "RDB\$RELATION_NAME"
			FROM "RDB\$RELATIONS"
			WHERE "RDB\$SYSTEM_FLAG"=0
			ORDER BY "RDB\$RELATION_NAME" ASC
SQL;
	}
	
	// --------------------------------------------------------------------------
	
	/**
	 * Returns sql to list system tables
	 *
	 * @return string
	 */
	public function system_table_list()
	{
		return <<<SQL
			SELECT "RDB\$RELATION_NAME"
			FROM "RDB\$RELATIONS"
			WHERE "RDB\$SYSTEM_FLAG"=1
			ORDER BY "RDB\$RELATION_NAME" ASC
SQL;
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
			SELECT DISTINCT "RDB\$VIEW_NAME"
			FROM "RDB\$VIEW_RELATIONS"
SQL;
	}
	
	// --------------------------------------------------------------------------
	
	/**
	 * Returns sql to list triggers
	 *
	 * @return string
	 */
	public function trigger_list()
	{
		return <<<SQL
			SELECT * FROM "RDB\$FUNCTIONS"
			WHERE "RDB\$SYSTEM_FLAG" = 0
SQL;
	}
	
	// --------------------------------------------------------------------------
	
	/**
	 * Return sql to list functions
	 *
	 * @return string
	 */
	public function function_list()
	{
		return 'SELECT * FROM "RDB$FUNCTIONS"';
	}
	
	// --------------------------------------------------------------------------
	
	/**
	 * Return sql to list stored procedures
	 *
	 * @return string
	 */
	public function procedure_list()
	{
		return <<<SQL
			SELECT "RDB\$PROCEDURE_NAME",
				"RDB\$PROCEDURE_ID",
				"RDB\$PROCEDURE_INPUTS",
				"RDB\$PROCEDURE_OUTPUTS",
				"RDB\$DESCRIPTION",
				"RDB\$PROCEDURE_SOURCE",
				"RDB\$SECURITY_CLASS",
				"RDB\$OWNER_NAME",
				"RDB\$RUNTIME",
				"RDB\$SYSTEM_FLAG",
				"RDB\$PROCEDURE_TYPE",
				"RDB\$VALID_BLR"
			FROM "RDB\$PROCEDURES"
			ORDER BY "RDB\$PROCEDURE_NAME" ASC
SQL;

	}
	
	// --------------------------------------------------------------------------
	
	/**
	 * Return sql to list sequences
	 *
	 * @return string
	 */
	public function sequence_list()
	{
		return <<<SQL
			SELECT "RDB\$GENERATOR_NAME"
			FROM "RDB\$GENERATORS"
			WHERE "RDB\$SYSTEM_FLAG" = 0
SQL;
	}
	
	// --------------------------------------------------------------------------
	
	/**
	 * Return sql to list columns of the specified table
	 * 
	 * @param string $table
	 * @return string 
	 */
	public function column_list($table)
	{
		return <<<SQL
			SELECT r.RDB\$FIELD_NAME AS field_name,
				r.RDB\$DESCRIPTION AS field_description,
				r.RDB\$DEFAULT_VALUE AS field_default_value,
				r.RDB\$NULL_FLAG AS field_not_null_constraint,
				f.RDB\$FIELD_LENGTH AS field_length,
				f.RDB\$FIELD_PRECISION AS field_precision,
				f.RDB\$FIELD_SCALE AS field_scale,
				CASE f.RDB\$FIELD_TYPE
					WHEN 261 THEN 'BLOB'
					WHEN 14 THEN 'CHAR'
					WHEN 40 THEN 'CSTRING'
					WHEN 11 THEN 'D_FLOAT'
					WHEN 27 THEN 'DOUBLE'
					WHEN 10 THEN 'FLOAT'
					WHEN 16 THEN 'INT64'
					WHEN 8 THEN 'INTEGER'
					WHEN 9 THEN 'QUAD'
					WHEN 7 THEN 'SMALLINT'
					WHEN 12 THEN 'DATE'
					WHEN 13 THEN 'TIME'
					WHEN 35 THEN 'TIMESTAMP'
					WHEN 37 THEN 'VARCHAR'
				ELSE 'UNKNOWN'
				END AS field_type,
				f.RDB\$FIELD_SUB_TYPE AS field_subtype,
				coll.RDB\$COLLATION_NAME AS field_collation,
				cset.RDB\$CHARACTER_SET_NAME AS field_charset
			FROM RDB\$RELATION_FIELDS r
			LEFT JOIN RDB\$FIELDS f ON r.RDB\$FIELD_SOURCE = f.RDB\$FIELD_NAME
			LEFT JOIN RDB\$COLLATIONS coll ON f.RDB\$COLLATION_ID = coll.RDB\$COLLATION_ID
			LEFT JOIN RDB\$CHARACTER_SETS cset ON f.RDB\$CHARACTER_SET_ID = cset.RDB\$CHARACTER_SET_ID
			WHERE r.RDB\$RELATION_NAME='{$table}'
			ORDER BY r.RDB\$FIELD_POSITION
SQL;
	}
	
	// --------------------------------------------------------------------------
	
	/**
	 * SQL to show list of field types
	 *
	 * @return string
	 */
	public function type_list()
	{
		return <<<SQL
			SELECT "RDB\$TYPE_NAME", "RDB\$FIELD_NAME" FROM "RDB\$TYPES"
			WHERE "RDB\$FIELD_NAME" IN ('RDB\$FIELD_TYPE', 'RDB\$FIELD_SUB_TYPE')
			ORDER BY "RDB\$FIELD_NAME" DESC, "RDB\$TYPE_NAME" ASC
SQL;
	}

}
//End of firebird_sql.php