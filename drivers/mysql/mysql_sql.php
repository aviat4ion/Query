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
 * MySQL specifc SQL
 */
class MySQL_SQL extends DB_SQL{

 	/**
 	 * Convienience public function for creating a new MySQL table
 	 *
 	 * @param string $name
 	 * @param array $columns
 	 * @param array $constraints=array()
 	 * @param array $indexes=array()
 	 *
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
				$column_array[$col]['constraint'] = "{$const} ({$col})";
			}
		}

		// Join column definitons together
		$columns = array();
		foreach($column_array as $n => $props)
		{
			$n = trim($n, '`');

			$str = "`{$n}` ";
			$str .= (isset($props['type'])) ? "{$props['type']} " : "";

			$columns[] = $str;
		}

		// Add constraints
		foreach($column_array as $n => $props)
		{
			if (isset($props['constraint']))
			{
				$columns[] = $props['constraint'];
			}
		}

		// Generate the sql for the creation of the table
		$sql = "CREATE TABLE IF NOT EXISTS `{$name}` (";
		$sql .= implode(", ", $columns);
		$sql .= ")";

		return $sql;
	}

	// --------------------------------------------------------------------------

	/**
	 * Convience public function for droping a MySQL table
	 *
	 * @param string $name
	 * @return  string
	 */
	public function delete_table($name)
	{
		return "DROP TABLE `{$name}`";
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
		return ' RAND()';
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
		return "SHOW DATABASES WHERE `Database` !='information_schema'";
	}

	// --------------------------------------------------------------------------

	/**
	 * Returns sql to list tables
	 *
	 * @return string
	 */
	public function table_list()
	{
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
	 * @return FALSE
	 */
	public function sequence_list()
	{
		return FALSE;
	}
}
//End of mysql_sql.php