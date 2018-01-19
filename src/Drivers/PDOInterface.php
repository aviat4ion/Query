<?php declare(strict_types=1);
/**
 * Query
 *
 * SQL Query Builder / Database Abstraction Layer
 *
 * PHP version 7.1
 *
 * @package     Query
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2012 - 2018 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link        https://git.timshomepage.net/aviat4ion/Query
 */
namespace Query\Drivers;

use PDO;
use PDOException;
use PDOStatement;

/**
 * Interface describing the PDO class in PHP
 */
interface PDOInterface {

	/**
	 * Creates a PDO instance representing a connection to a database
	 *
	 * @param string $dsn
	 * @param string $username
	 * @param string $password
	 * @param array $options
	 * @throws PDOException
	 */
	public function __construct($dsn, $username, $password, array $options = []);

	/**
	 * Initiates a transaction
	 *
	 * @throws PDOException
	 * @return boolean
	 */
	public function beginTransaction();

	/**
	 * Commits a transaction
	 *
	 * @throws PDOException
	 * @return boolean
	 */
	public function commit();

	/**
	 * Fetch the SQLSTATE associated with the last operation on the database handle
	 *
	 * @return mixed
	 */
	public function errorCode();

	/**
	 * Fetch extended error information associated with the last operation on the database handle
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
	 * Retrieve a database connection attribute
	 *
	 * @param int $attribute
	 * @return mixed
	 */
	public function getAttribute($attribute);

	/**
	 * Return an array of available PDO drivers
	 *
	 * @return array
	 */
	public static function getAvailableDrivers();

	/**
	 * Checks if inside a transaction
	 *
	 * @return boolean
	 */
	public function inTransaction();

	/**
	 * Returns teh ID of the last inserted row or sequence value
	 *
	 * @param string $name Name of the sequence object from which the ID should be returned
	 * @return string
	 */
	public function lastInsertId($name = NULL);

	/**
	 * Prepares a statement for execution and returns a statement object
	 *
	 * @param string $statement
	 * @param array $options
	 * @return PDOStatement
	 */
	public function prepare($statement, $options = NULL);

	/**
	 * Executes an SQL statement, returning a result set as a PDOStatement object
	 *
	 * @return PDOStatement
	 */
	public function query();

	/**
	 * Quotes a string for use in a query
	 *
	 * @param string $string
	 * @param int $parameterType
	 * @return string|false
	 */
	public function quote($string, $parameterType = PDO::PARAM_STR);

	/**
	 * Rolls back a transaction
	 *
	 * @throws PDOException
	 * @return boolean
	 */
	public function rollBack();

	/**
	 * Set an attribute
	 *
	 * @param int $attribute
	 * @param mixed $value
	 * @return boolean
	 */
	public function setAttribute($attribute, $value);
}