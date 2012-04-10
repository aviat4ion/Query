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
 * PostgreSQL specifc class
 *
 * @extends DB_PDO
 */
class pgSQL extends DB_PDO {

	/**
	 * Connect to a PosgreSQL database
	 *
	 * @param string $dsn
	 * @param string $username=null
	 * @param string $password=null
	 * @param array  $options=array()
	 */
	public function __construct($dsn, $username=null, $password=null, $options=array())
	{
		parent::__construct("pgsql:$dsn", $username, $password, $options);

		//Get db manip class
		$class = __CLASS__.'_sql';
		$this->sql = new $class;
	}

	// --------------------------------------------------------------------------

	/**
	 * Connect to a different database
	 *
	 * @param string $name
	 */
	public function switch_db($name)
	{
		// @todo Implement
		return FALSE;
	}

	// --------------------------------------------------------------------------

	/**
	 * Empty a table
	 *
	 * @param string $table
	 */
	public function truncate($table)
	{
		$sql = 'TRUNCATE "' . $table . '"';
		$this->query($sql);
	}

	// --------------------------------------------------------------------------

	/**
	 * Return the number of rows returned for a SELECT query
	 *
	 * @return int
	 */
	public function num_rows()
	{
		return (isset($this->statement)) ? $this->statement->rowCount : FALSE;
	}

	// --------------------------------------------------------------------------

	/**
	 * Get a list of schemas for the current connection
	 *
	 * @return array
	 */
	public function get_schemas()
	{
		$sql = <<<SQL
			SELECT DISTINCT "schemaname" FROM "pg_tables"
			WHERE "schemaname" NOT LIKE 'pg\_%'
			AND "schemaname" != 'information_schema'
SQL;

		return $this->driver_query($sql);
	}
}
//End of pgsql_driver.php