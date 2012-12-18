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
 * PostgreSQL specifc class
 *
 * @package Query
 * @subpackage Drivers
 */
class PgSQL extends DB_PDO {

	/**
	 * Connect to a PosgreSQL database
	 *
	 * @param string $dsn
	 * @param string $username
	 * @param string $password
	 * @param array  $options
	 */
	public function __construct($dsn, $username=null, $password=null, $options=array())
	{
		if (strpos($dsn, 'pgsql') === FALSE)
		{
			$dsn = 'pgsql:'.$dsn;
		}
	
		parent::__construct($dsn, $username, $password, $options);
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