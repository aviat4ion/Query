<?php
/**
 * Query
 *
 * SQL Query Builder / Database Abstraction Layer
 *
 * PHP version 5.4
 *
 * @package     Query
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2012 - 2015 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link        https://git.timshomepage.net/aviat4ion/Query
 */

namespace Query\Drivers\Firebird;

use PDOStatement;
use Query\Drivers\PDOStatementInterface;

/**
 * Firebird result class to emulate PDOStatement Class - only implements
 * data-fetching methods
 *
 * @package Query
 * @subpackage Drivers
 */
class Result extends PDOStatement implements PDOStatementInterface {

	/**
	 * Reference to fbird resource
	 *
	 * @var resource
	 */
	private $statement;

	/**
	 * Current row in result array
	 *
	 * @var integer
	 */
	private $row;

	/**
	 * Data pulled from query
	 *
	 * @var mixed
	 */
	private $result = [];

	/**
	 * Reference to the db drive to de-duplicate error functions
	 *
	 * @var Driver
	 */
	private $db;

	/**
	 * Create the object by passing the resource for
	 * the query
	 *
	 * @param resource $link
	 * @param Driver|null $db
	 */
	public function __construct($link, Driver $db = NULL)
	{
		if ( ! is_null($db))
		{
			$this->db = $db;
		}
		$this->statement = $link;
		$this->setFetchMode(\PDO::FETCH_ASSOC);
		$this->row = -1;
		$this->result = [];

		// Create the result array, so that we can get row counts
		// Check the resource type, because prepared statements are "interbase query"
		// but we only want "interbase result" types when attempting to fetch data
		if (\is_resource($link) && \get_resource_type($link) === "interbase result")
		{
			while($row = \fbird_fetch_assoc($link, \IBASE_FETCH_BLOBS))
			{
				$this->result[] = $row;
			}

			// Free the result resource
			\fbird_free_result($link);
		}
	}

	// --------------------------------------------------------------------------

	/**
	 * Invalidate method for data consistency
	 *
	 * @param mixed $column
	 * @param mixed $param
	 * @param int $type
	 * @param mixed $maxlen
	 * @param array $driverdata
	 * @return NULL
	 */
	public function bindColumn($column, &$param, $type=NULL, $maxlen=NULL, $driverdata=NULL)
	{
		return NULL;
	}

	// --------------------------------------------------------------------------

	/**
	 * Invalidate method for data consistency
	 *
	 * @param mixed $parameter
	 * @param mixed $variable
	 * @param int $data_type
	 * @param mixed $maxlen
	 * @param array $driverdata
	 * @return NULL
	 */
	public function bindParam($parameter, &$variable, $data_type=NULL, $maxlen=NULL, $driverdata=NULL)
	{
		return NULL;
	}

	// --------------------------------------------------------------------------

	/**
	 * Invalidate method for data consistency
	 *
	 * @param mixed $parameter
	 * @param mixed $variable
	 * @param int $data_type
	 * @return NULL
	 */
	public function bindValue($parameter, $variable, $data_type=NULL)
	{
		return NULL;
	}

	// --------------------------------------------------------------------------

	/**
	 * Run a prepared statement query
	 *
	 * @param  array $bound_input_params
	 * @return Result
	 */
	public function execute($bound_input_params = NULL)
	{
		//Add the prepared statement as the first parameter
		\array_unshift($bound_input_params, $this->statement);

		// Let php do all the hard stuff in converting
		// the array of arguments into a list of arguments
		// Then pass the resource to the constructor
		$this->__construct(\call_user_func_array('fbird_execute', $bound_input_params));

		return $this;
	}

	// --------------------------------------------------------------------------

	/**
	 * Emulate PDO fetch public function
	 *
	 * @param int $fetch_style
	 * @param mixed $cursor_orientation
	 * @param mixed $cursor_offset
	 * @return mixed
	 */
	public function fetch($fetch_style=\PDO::FETCH_ASSOC, $cursor_orientation = \PDO::FETCH_ORI_NEXT, $cursor_offset=NULL)
	{
		// If there is no result, continue
		if (empty($this->result))
		{
			return NULL;
		}

		// Keep track of the current row being fetched
		++$this->row;

		// return NULL if the next row doesn't exist
		if ( ! isset($this->result[$this->row]))
		{
			return NULL;
		}

		switch($fetch_style)
		{
			case \PDO::FETCH_OBJ:
				$row = (object) $this->result[$this->row];
			break;

			case \PDO::FETCH_NUM:
				$row = \array_values($this->result[$this->row]);
			break;

			default:
				$row = $this->result[$this->row];
			break;
		}

		return $row;
	}

	// --------------------------------------------------------------------------

	/**
	 * Emulate PDO fetchAll public function
	 *
	 * @param int  $fetch_style
	 * @param mixed $statement
	 * @param mixed $ctor_args
	 * @return mixed
	 */
	public function fetchAll($fetch_style=\PDO::FETCH_ASSOC, $statement=NULL, $ctor_args=NULL)
	{
		$all = [];

		while($row = $this->fetch($fetch_style, $statement))
		{
			$all[] = $row;
		}

		$this->result = $all;

		return $all;
	}

	// --------------------------------------------------------------------------

	/**
	 * Emulate PDOStatement::fetchColumn
	 *
	 * @param int $column_num
	 * @return mixed
	 */
	public function fetchColumn($column_num=0)
	{
		$row = $this->fetch(\PDO::FETCH_NUM);
		return $row[$column_num];
	}

	// --------------------------------------------------------------------------

	/**
	 * Emulate PDOStatement::fetchObject, but only for the default use
	 *
	 * @param string $class_name
	 * @param array|null $ctor_args
	 * @return object
	 */
	public function fetchObject($class_name='stdClass', $ctor_args=NULL)
	{
		return $this->fetch(\PDO::FETCH_OBJ);
	}

	// --------------------------------------------------------------------------

	/**
	 * Return the number of rows affected by the previous query
	 *
	 * @return int
	 */
	public function rowCount()
	{
		return \fbird_affected_rows();
	}

	// --------------------------------------------------------------------------

	/**
	 * Method to emulate PDOStatement->errorCode
	 *
	 * @return string
	 */
	public function errorCode()
	{
		return $this->db->errorCode();
	}

	// --------------------------------------------------------------------------

	/**
	 * Method to emulate PDO->errorInfo / PDOStatement->errorInfo
	 *
	 * @return array
	 */
	public function errorInfo()
	{
		return $this->db->errorInfo();
	}
}
// End of firebird_result.php