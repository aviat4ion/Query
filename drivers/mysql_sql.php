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
 	 * @param [type] $name [description]
 	 * @param [type] $columns [description]
 	 * @param array $constraints=array() [description]
 	 * @param array $indexes=array() [description]
 	 * 
 	 * @return [type]
 	 */
	public function create_table($name, $columns, array $constraints=array(), array $indexes=array())
	{
		//TODO: implement
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
}
//End of mysql_sql.php