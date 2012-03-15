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
	
	public function create_table($name, $columns, array $constraints=array(), array $indexes=array())
	{
		//TODO: implement
	}
	
	// --------------------------------------------------------------------------

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

}
//End of pgsql_manip.php