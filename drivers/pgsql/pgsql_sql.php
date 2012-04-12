<?php
/**
 * Query
 *
 * Free Query Builder / Database Abstraction Layer
 *
 * @author 		Timothy J. Warren
 * @copyright	Copyright (c) 2012
 * @link 		https://github.com/aviat4ion/Query
 * @license 	http://philsturgeon.co.uk/code/dbad-license 
 */

// --------------------------------------------------------------------------

/**
 * PostgreSQL specifc SQL
 */
class pgSQL_SQL extends DB_SQL {

	/**
	 * Database-specific method to create a new table
	 *
	 * @param string $name
	 * @param array $columns
	 * @param array $constraints
	 * @param array $indexes
	 * @return string
	 */
	public function create_table($name, $columns, array $constraints=array(), array $indexes=array())
	{
		$column_array = array();

		// Reorganize into an array indexed with column information
		// Eg $column_array[$colname] = array(
		// 		'type' => ...,
		// 		'constraint' => ...,
		// 		'index' => ...,
		// )
		foreach($columns as $colname => $type)
		{
			if(is_numeric($colname))
			{
				$colname = $type;
			}

			$column_array[$colname] = array();
			$column_array[$colname]['type'] = ($type !== $colname) ? $type : '';
		}

		if( ! empty($constraints))
		{
			foreach($constraints as $col => $const)
			{
				$column_array[$col]['constraint'] = $const;
			}
		}

		// Join column definitons together
		$columns = array();
		foreach($column_array as $n => $props)
		{
			$str = "{$n} ";
			$str .= (isset($props['type'])) ? "{$props['type']} " : "";
			$str .= (isset($props['constraint'])) ? $props['constraint'] : "";

			$columns[] = $str;
		}

		// Generate the sql for the creation of the table
		$sql = "CREATE TABLE \"{$name}\" (";
		$sql .= implode(", ", $columns);
		$sql .= ")";

		return $sql;
	}

	// --------------------------------------------------------------------------

	/**
	 * Database-specific SQL for dropping a table
	 *
	 * @param string $name
	 * @return string
	 */
	public function delete_table($name)
	{
		return 'DROP TABLE "'.$name.'"';
	}

	// --------------------------------------------------------------------------

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
		$sql .= " LIMIT {$limit}";

		if(is_numeric($offset))
		{
			$sql .= " OFFSET {$offset}";
		}

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
		return ' RANDOM()';
	}

	// --------------------------------------------------------------------------

	/**
	 * Create an SQL backup file for the current database's structure
	 *
	 * @return string
	 */
	public function backup_structure()
	{
		// @todo Implement Backup function
		return '';
	}

	// --------------------------------------------------------------------------

	/**
	 * Create an SQL backup file for the current database's data
	 *
	 * @return string
	 */
	public function backup_data()
	{
		// @todo Implement Backup function
		return '';
	}

	// --------------------------------------------------------------------------

	/**
	 * Returns sql to list other databases
	 *
	 * @return string
	 */
	public function db_list()
	{
		return <<<SQL
			SELECT "datname" FROM "pg_database"
			WHERE "datname" NOT IN ('template0','template1')
			ORDER BY "datname" ASC
SQL;
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
			SELECT "tablename" FROM "pg_tables"
			WHERE "tablename" NOT LIKE 'pg_%'
			AND "tablename" NOT LIKE 'sql_%'
			ORDER BY "tablename" ASC
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
		 	SELECT "tablename" FROM "pg_tables"
			WHERE "tablename" LIKE 'pg\_%'
			OR "tablename" LIKE 'sql\%'
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
		 	SELECT "viewname" FROM "pg_views"
			WHERE "schemaname" NOT IN
				('pg_catalog', 'information_schema')
			AND "viewname" !~ '^pg_'
			ORDER BY "viewname" ASC
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
			SELECT * 
			FROM "information_schema"."triggers"
			WHERE "trigger_schema" NOT IN
				('pg_catalog', 'information_schema')
SQL;
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
	 * @return string
	 */
	public function procedure_list()
	{
		return <<<SQL
			SELECT "routine_name"
			FROM "information_schema"."routines"
			WHERE "specific_schema" NOT IN
				('pg_catalog', 'information_schema')
			AND "type_udt_name" != 'trigger';
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
			SELECT "c"."relname"
			FROM "pg_class" "c"
			WHERE "c"."relkind" = 'S'
			ORDER BY "relname" ASC
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
			SELECT ordinal_position,
				column_name,
				data_type,
				column_default,
				is_nullable,
				character_maximum_length,
				numeric_precision
			FROM information_schema.columns
			WHERE table_name = '{$table}'
			ORDER BY ordinal_position;
SQL;
	}
}
//End of pgsql_manip.php