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
}
//End of firebird_sql.php