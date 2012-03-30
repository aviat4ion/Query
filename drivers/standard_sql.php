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
 * Dummy class for standardized sql
 */
class Standard_SQL extends DB_SQL {

	/**
	 * Convenience public function to create a new table
	 *
	 * @param string $name //Name of the table
	 * @param array $columns //columns as straight array and/or column => type pairs
	 * @param array $constraints // column => constraint pairs
	 * @param array $indexes // column => index pairs
	 * @return  string
	 */
	public function create_table($names, $columns, array $constraints=array(), array $indexes=array())
	{
		// @todo Implement
	}

	// --------------------------------------------------------------------------

	/**
	 * SQL to drop the specified table
	 *
	 * @param string $name
	 * @return string
	 */
	public function delete_table($name)
	{
		// @todo Implement
	}

	// --------------------------------------------------------------------------

	/**
	 * Random ordering keyword
	 *
	 * @return string
	 */
	public function random()
	{
		// @todo check if standardized
		return FALSE;
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
		if (is_numeric($offset))
		{
			$sql .= ' OFFSET '.$offset.' ROWS ';
		}

		$sql .= ' FETCH FIRST '.$limit.' ROWS ONLY ';
	}
}
// End of standard_sql.php