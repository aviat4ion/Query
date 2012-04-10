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
 * Firebird Specific SQL
 */
class Firebird_SQL extends DB_SQL {

	/**
	 * Convienience public function to generate sql for creating a db table
	 *
	 * @param string $name
	 * @param array $fields
	 * @param array $constraints=array()
	 * @param array $indexes=array()
	 *
	 * @return string
	 */
	public function create_table($name, $fields, array $constraints=array(), array $indexes=array())
	{
		$column_array = array();

		// Reorganize into an array indexed with column information
		// Eg $column_array[$colname] = array(
		// 		'type' => ...,
		// 		'constraint' => ...,
		// 		'index' => ...,
		// )
		foreach($fields as $colname => $type)
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
			$str = '"'.$n.'" ';
			$str .= (isset($props['type'])) ? "{$props['type']} " : "";
			$str .= (isset($props['constraint'])) ? "{$props['constraint']} " : "";

			$columns[] = $str;
		}

		// Generate the sql for the creation of the table
		$sql = 'CREATE TABLE "'.$name.'" (';
		$sql .= implode(',', $columns);
		$sql .= ')';

		return $sql;
	}

	// --------------------------------------------------------------------------

	/**
	 * Drop the selected table
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
	 * Create an SQL backup file for the current database's structure
	 *
	 * @return string
	 */
	public function backup_structure()
	{
		// @todo Implement Backup structure function
		return '';
	}

	// --------------------------------------------------------------------------

	/**
	 * Create an SQL backup file for the current database's data
	 *
	 * @param array $exclude
	 * @param bool $system_tables
	 * @return string
	 */
	public function backup_data($exclude=array(), $system_tables=FALSE)
	{
		// Determine which tables to use
		if($system_tables == TRUE)
		{
			$tables = array_merge($this->get_system_tables(), $this->get_tables());
		}
		else
		{
			$tables = $this->get_tables();
		}

		// Filter out the tables you don't want
		if( ! empty($exclude))
		{
			$tables = array_diff($tables, $exclude);
		}

		$output_sql = '';

		// Get the data for each object
		foreach($tables as $t)
		{
			$sql = 'SELECT * FROM "'.trim($t).'"';
			$res = $this->query($sql);
			$obj_res = $this->fetchAll(PDO::FETCH_ASSOC);

			unset($res);

			// Nab the column names by getting the keys of the first row
			$columns = @array_keys($obj_res[0]);

			$insert_rows = array();

			// Create the insert statements
			foreach($obj_res as $row)
			{
				$row = array_values($row);

				// Quote values as needed by type
				if(stripos($t, 'RDB$') === FALSE)
				{
					$row = array_map(array(&$this, 'quote'), $row);
					$row = array_map('trim', $row);
				}

				$row_string = 'INSERT INTO "'.trim($t).'" ("'.implode('","', $columns).'") VALUES ('.implode(',', $row).');';

				unset($row);

				$insert_rows[] = $row_string;
			}

			unset($obj_res);

			$output_sql .= "\n\nSET TRANSACTION;\n".implode("\n", $insert_rows)."\nCOMMIT;";
		}

		return $output_sql;
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
			SELECT "RDB\$RELATION_NAME" FROM "RDB\$RELATIONS"
			WHERE "RDB\$RELATION_NAME" NOT LIKE 'RDB$%'
			AND "RDB\$RELATION_NAME" NOT LIKE 'MON$%'
			AND "RDB\$VIEW_BLR" IS NOT NULL
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
			SELECT "RDB\$RELATION_NAME" FROM "RDB\$RELATIONS"
			WHERE "RDB\$RELATION_NAME" LIKE 'RDB$%'
			OR "RDB\$RELATION_NAME" LIKE 'MON$%';
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
			SELECT "RDB\$RELATION_NAME"
			FROM "RDB\$RELATIONS"
			WHERE "RDB\$VIEW_BLR" IS NOT NULL
			AND ("RDB\$SYSTEM_FLAG" IS NULL OR "RDB\$SYSTEM_FLAG" = 0)
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
		return <<<SQL
			SELECT * FROM "RDB\$FUNCTIONS"
			WHERE "RDB\$SYSTEM_FLAG" = 0
SQL;
	}
	
	// --------------------------------------------------------------------------
	
	/**
	 * Return sql to list stored procedures
	 *
	 * @return string
	 */
	public function procedure_list()
	{
		return 'SELECT * FROM "RDB$PROCEDURES"';
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
}
//End of firebird_sql.php