<?php
/**
 * Query
 *
 * Free Query Builder / Database Abstraction Layer
 *
 * @package		Query
 * @author		Timothy J. Warren
 * @copyright	Copyright (c) 2012 - 2014
 * @link 		https://github.com/aviat4ion/Query
 * @license		http://philsturgeon.co.uk/code/dbad-license
 */

// --------------------------------------------------------------------------

namespace Query\Driver;

/**
 * PDO Interface to implement for database drivers
 *
 * @package Query
 * @subpackage Drivers
 */
interface Driver_Interface {

	/**
	 * Constructor/Connection method
	 *
	 * @param string $dsn
	 * @param string $username
	 * @param string $password
	 * @param array $driver_options
	 */
	public function __construct($dsn, $username=NULL, $password=NULL, array $driver_options = array());

	/**
	 * Begin a transaction
	 *
	 * @return bool
	 */
	public function beginTransaction();

	/**
	 * Commit a transaction
	 *
	 * @return bool
	 */
	public function commit();

	/**
	 * Return the current error code
	 *
	 * @return mixed
	 */
	public function errorCode();

	/**
	 * Return information about the current error
	 *
	 * @return array
	 */
	public function errorInfo();

	/**
	 * Execute an SQL statement and return the number of affected rows
	 *
	 * @param string $statement
	 * @return int
	 */
	public function exec($statement);

	/**
	 *  Get a connection attribute for the current db driver
	 *
	 * @param int $attribute
	 * @return mixed
	 */
	public function getAttribute($attribute);

	/**
	 * Rollback a transaction
	 *
	 * @return bool
	 */
	public function rollback();

	/**
	 * Set a connection attribute
	 * @param int $attribute
	 * @param mixed $value
	 * @return bool
	 */
	public function setAttribute($attribute, $value);

	/**
	 * Retrieve column information for the current database table
	 *
	 * @param string $table
	 * @return array
	 */
	public function get_columns($table);

	/**
	 * Retrieve foreign keys for the table
	 *
	 * @param string $table
	 * @return array
	 */
	public function get_fks($table);

	/**
	 * Return list of tables for the current database
	 *
	 * @return array
	 */
	public function get_tables();

	/**
	 * Surrounds the string with the databases identifier escape characters
	 *
	 * @param mixed $ident
	 * @return string
	 */
	public function quote_ident($ident);

	/**
	 * Quote database table name, and set prefix
	 *
	 * @param string $table
	 * @return string
	 */
	public function quote_table($table);

	/**
	 * Create and execute a prepared statement with the provided parameters
	 *
	 * @param string $sql
	 * @param array $params
	 * @return \PDOStatement
	 */
	public function prepare_execute($sql, $params);

	/**
	 * Get the SQL class for the current driver
	 *
	 * @return SQL_Interface
	 */
	public function get_sql();

	/**
	 * Get the Util class for the current driver
	 *
	 * @return Abstract_Util
	 */
	public function get_util();
}
// End of driver_interface.php
