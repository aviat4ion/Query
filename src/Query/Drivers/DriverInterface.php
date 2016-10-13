<?php declare(strict_types=1);
/**
 * Query
 *
 * SQL Query Builder / Database Abstraction Layer
 *
 * PHP version 7
 *
 * @package     Query
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2012 - 2016 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link        https://git.timshomepage.net/aviat4ion/Query
 */


namespace Query\Drivers;

/**
 * PDO Interface to implement for database drivers
 *
 * @package Query
 * @subpackage Drivers
 */
interface DriverInterface extends PDOInterface {

	/**
	 * Constructor/Connection method
	 *
	 * @param string $dsn
	 * @param string $username
	 * @param string $password
	 * @param array $driver_options
	 */
	public function __construct($dsn, $username=NULL, $password=NULL, array $driver_options = []);

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
	 * @param string|array $ident
	 * @return string|array
	 */
	public function quote_ident($ident);

	/**
	 * Quote database table name, and set prefix
	 *
	 * @param string|array $table
	 * @return string|array
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
	public function insert_batch($table, $data=[]);

	/**
	 * Creates a batch update, and executes it.
	 * Returns the number of affected rows
	 *
	 * @param string $table
	 * @param array|object $data
	 * @param string $where
	 * @return int|null
	 */
	public function update_batch($table, $data, $where);

	/**
	 * Get the SQL class for the current driver
	 *
	 * @return \Query\Drivers\SQLInterface
	 */
	public function get_sql();

	/**
	 * Get the Util class for the current driver
	 *
	 * @return AbstractUtil
	 */
	public function get_util();

	/**
	 * Set the last query sql
	 *
	 * @param string $query_string
	 * @return void
	 */
	public function set_last_query(string $query_string);
}
// End of driver_interface.php