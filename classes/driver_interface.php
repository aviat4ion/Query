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
	 * @param [string] $username
	 * @param [string] $password
	 * @param [array] $driver_options
	 * @return void
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
	 * @returm mixed
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
}
// End of driver_interface.php
