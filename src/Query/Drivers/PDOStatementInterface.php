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

use PDO;

/**
 * Interface created from official PHP Documentation
 */
interface PDOStatementInterface {

	/**
	 * Bind a column to a PHP variable
	 *
	 * @param mixed $column Number or name of the column in the result set
	 * @param mixed $param Name of the PHP variable to which the column will be bound
	 * @param int $type Data type of the parameter, specified by the PDO::PARAM_* constants
	 * @param int $maxlen A hint for pre-allocation
	 * @param mixed $driverdata Optional parameter(s) for the driver
	 * @return boolean
	 */
	public function bindColumn($column, &$param, $type, $maxlen, $driverdata);

	/**
	 * Binds a parameter to the specified variable name
	 *
	 * @param mixed $parameter Parameter identifier. For a prepared statement using named placeholders, this will be a
	 * parameter name of the form :name. For a prepared statement using question mark placeholders, this will be the
	 * 1-indexed position of the parameter.
	 * @param mixed $variable Name of the PHP variable to bind to the SQL statement parameter.
	 * @param int $data_type Explicit data type for the parameter using the PDO::PARAM_* constants. To return an INOUT
	 * parameter from a stored procedure, use the bitwise OR operator to set the PDO::PARAM_INPUT_OUTPUT bits
	 * for the data_type parameter.
	 * @param int $length Length of the data type. To indicate that a parameter is an OUT parameter from a stored procedure,
	 * you must explicitly set the length.
	 * @param mixed $driver_options
	 * @return boolean
	 */
	public function bindParam($parameter, &$variable, $data_type = PDO::PARAM_STR, $length, $driver_options);

	/**
	 * Binds a value to a corresponding named or question mark placeholder in the SQL statement that was used to
	 * prepare the statement
	 *
	 * @param mixed $parameter Parameter identifier. For a prepared statement using named placeholders, this will be a
	 * parameter name of the form :name. For a prepared statement using question mark placeholders, this will be the
	 * 1-indexed position of the parameter.
	 * @param mixed $value The value to bind to the parameter
	 * @param int $data_type Explicit data type for the parameter using the PDO::PARAM_* constants.
	 * @return boolean
	 */
	public function bindValue($parameter, $value, $data_type = PDO::PARAM_STR);

	/**
	 * Frees up the connection to the server so that other SQL statements may be issued, but leaves the statement in a
	 * state that enables it to be executed again
	 *
	 * @return boolean
	 */
	public function closeCursor();

	/**
	 * Returns the number of columns in the result set
	 *
	 * @return int
	 */
	public function columnCount();

	/**
	 * Dumps the information contained by a prepared statement directly on the output
	 *
	 * @return void
	 */
	public function debugDumpParams();

	/**
	 * Fetch the SQLSTATE associated with the last operation on the statement handle
	 *
	 * @return string
	 */
	public function errorCode();

	/**
	 * Fetch extended error information associated with the last operation on the statement handle
	 *
	 * @return array
	 */
	public function errorInfo();

	/**
	 * Run a prepared statement query
	 *
	 * @param  array $bound_input_params
	 * @return boolean
	 */
	public function execute($bound_input_params = NULL);

	/**
	 * Fetches the next row from a result set
	 *
	 * @param int $how
	 * @param int $orientation
	 * @param int $offset
	 * @return mixed
	 */
	public function fetch($how = PDO::ATTR_DEFAULT_FETCH_MODE, $orientation = PDO::FETCH_ORI_NEXT, $offset = 0);

	/**
	 * Returns a single column from the next row of a result set
	 *
	 * @param int $column_number
	 * @return mixed
	 */
	public function fetchColumn($column_number = 0);

	/**
	 * Fetches the next row and returns it as an object
	 *
	 * @param string $class_name
	 * @param array $ctor_args
	 * @return mixed
	 */
	public function fetchObject($class_name = "stdClass", $ctor_args = NULL);

	/**
	 * Retrieve a statement attribute
	 *
	 * @param int $attribute
	 * @return mixed
	 */
	public function getAttribute($attribute);

	/**
	 * Advances to the next rowset in a multi-rowset statement handle
	 *
	 * @return boolean
	 */
	public function nextRowset();

	/**
	 * Returns the number of rows affected by the last SQL statement
	 *
	 * @return int
	 */
	public function rowCount();

	/**
	 * Set a statement attribute
	 *
	 * @param int $attribute
	 * @param mixed $value
	 * @return boolean
	 */
	public function setAttribute($attribute, $value);

	/**
	 * Set the default fetch mode for this statement
	 *
	 * @param int $mode
	 * @return boolean
	 */
	public function setFetchMode($mode);
}