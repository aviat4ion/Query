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
	 * @return void
	 */
	public function __construct($dsn, $username=NULL, $password=NULL, array $driver_options = array());

	/**
	 * Simplifies prepared statements for database queries
	 *
	 * @param string $sql
	 * @param array $data
	 * @return \PDOStatement | FALSE
	 * @throws \InvalidArgumentException
	 */
	public function prepare_query($sql, $data);

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
	 * Retrieve list of data types for the database
	 *
	 * @return array
	 */
	public function get_types();

	/**
	 * Retrieve indexes for the table
	 *
	 * @param string $table
	 * @return array
	 */
	public function get_indexes($table);

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
	 * Retrieves an array of non-user-created tables for
	 * the connection/database
	 *
	 * @return array
	 */
	public function get_system_tables();

	/**
	 * Return list of dbs for the current connection, if possible
	 *
	 * @return array
	 */
	public function get_dbs();

	/**
	 * Return list of views for the current database
	 *
	 * @return array
	 */
	public function get_views();

	/**
	 * Return list of sequences for the current database, if they exist
	 *
	 * @return array
	 */
	public function get_sequences();

	/**
	 * Return list of functions for the current database
	 *
	 * @return array
	 */
	public function get_functions();

	/**
	 * Return list of stored procedures for the current database
	 *
	 * @return array
	 */
	public function get_procedures();

	/**
	 * Return list of triggers for the current database
	 *
	 * @return array
	 */
	public function get_triggers();

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
	 * @return SQL\SQL_Interface
	 */
	public function get_sql();

	/**
	 * Get the Util class for the current driver
	 *
	 * @return Util\Abstract_Util
	 */
	public function get_util();

	/**
	 * Method to simplify retrieving db results for meta-data queries
	 *
	 * @param string|array|null $query
	 * @param bool $filtered_index
	 * @return array
	 */
	public function driver_query($query, $filtered_index=TRUE);

	/**
	 * Returns number of rows affected by an INSERT, UPDATE, DELETE type query
	 *
	 * @return int
	 */
	public function affected_rows();

	/**
	 * Return the number of rows returned for a SELECT query
	 * @see http://us3.php.net/manual/en/pdostatement.rowcount.php#87110
	 *
	 * @return int
	 */
	public function num_rows();

	/**
	 * Prefixes a table if it is not already prefixed
	 *
	 * @param string $table
	 * @return string
	 */
	public function prefix_table($table);

	/**
	 * Create sql for batch insert
	 *
	 * @param string $table
	 * @param array $data
	 * @return array
	 */
	public function insert_batch($table, $data=array());
}
// End of driver_interface.php
