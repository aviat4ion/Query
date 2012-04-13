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
 * Abstract parent for database manipulation subclasses
 */
abstract class DB_SQL {

	// --------------------------------------------------------------------------
	// ! Methods to override
	// --------------------------------------------------------------------------

	/**
	 * Get the max keyword sql
	 *
	 * @return string
	 */
	public function max()
	{
		return ' MAX';
	}
	
	// --------------------------------------------------------------------------
	
	/**
	 * Get the min keyword sql
	 *
	 * @return string
	 */
	public function min()
	{
		return ' MIN';
	}
	
	// --------------------------------------------------------------------------
	
	/**
	 * Get the 'distinct' keyword 
	 *
	 * @return string
	 */
	public function distinct()
	{
		return ' DISTINCT ';
	}
	
	// --------------------------------------------------------------------------
	
	/**
	 * Get the 'average' keyword
	 *
	 * @return string
	 */
	public function avg()
	{
		return ' AVG';
	}
	
	// --------------------------------------------------------------------------
	
	/**
	 * Get the 'sum' keyword
	 *
	 * @return string
	 */
	public function sum()
	{
		return ' SUM';
	}
	
	// --------------------------------------------------------------------------
	// ! Abstract Methods
	// --------------------------------------------------------------------------

	/**
	 * Get database-specific sql to create a new table
	 *
	 * @abstract
	 * @param string $name
	 * @param array $columns
	 * @param array $constraints
	 * @param array $indexes
	 * @return string
	 */
	abstract public function create_table($name, $columns, array $constraints=array(), array $indexes=array());

	/**
	 * Get database-specific sql to drop a table
	 *
	 * @abstract
	 * @param string $name
	 * @return string
	 */
	abstract public function delete_table($name);

	/**
	 * Get database specific sql for limit clause
	 *
	 * @abstract
	 * @param string $sql
	 * @param int $limiit
	 * @param int $offset
	 * @return string
	 */
	abstract public function limit($sql, $limit, $offset=FALSE);

	/**
	 * Get the sql for random ordering
	 *
	 * @abstract
	 * @return string
	 */
	abstract public function random();

	/**
	 * Return an SQL file with the database table structure
	 *
	 * @abstract
	 * @return string
	 */
	abstract public function backup_structure();

	/**
	 * Return an SQL file with the database data as insert statements
	 *
	 * @abstract
	 * @return string
	 */
	abstract public function backup_data();
	
	/**
	 * Returns sql to list other databases
	 *
	 * @return string
	 */
	abstract public function db_list();

	/**
	 * Returns sql to list tables
	 *
	 * @return string
	 */
	abstract public function table_list();

	/**
	 * Returns sql to list system tables
	 *
	 * @return string
	 */
	abstract public function system_table_list();

	/**
	 * Returns sql to list views
	 *
	 * @return string
	 */
	abstract public function view_list();

	/**
	 * Returns sql to list triggers
	 *
	 * @return string
	 */
	abstract public function trigger_list();

	/**
	 * Return sql to list functions
	 *
	 * @return FALSE
	 */
	abstract public function function_list();

	/**
	 * Return sql to list stored procedures
	 *
	 * @return string
	 */
	abstract public function procedure_list();

	/**
	 * Return sql to list sequences
	 *
	 * @return string
	 */
	abstract public function sequence_list();
}
// End of db_sql.php