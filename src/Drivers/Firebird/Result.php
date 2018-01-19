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

	/**
	 * Invalidate method for data consistency
	 *
	 * @param mixed $parameter
	 * @param mixed $variable
	 * @param int $dataType
	 * @param mixed $maxlen
	 * @param array $driverdata
	 * @return NULL
	 */
	public function bindParam($parameter, &$variable, $dataType=NULL, $maxlen=NULL, $driverdata=NULL)
	{
		return NULL;
	}

	/**
	 * Invalidate method for data consistency
	 *
	 * @param mixed $parameter
	 * @param mixed $variable
	 * @param int $dataType
	 * @return NULL
	 */
	public function bindValue($parameter, $variable, $dataType=NULL)
	{
		return NULL;
	}

	/**
	 * Run a prepared statement query
	 *
	 * @param  array $boundInputParams
	 * @return Result
	 */
	public function execute($boundInputParams = NULL)
	{
		//Add the prepared statement as the first parameter
		\array_unshift($boundInputParams, $this->statement);

		// Let php do all the hard stuff in converting
		// the array of arguments into a list of arguments
		// Then pass the resource to the constructor
		$this->__construct(\call_user_func_array('fbird_execute', $boundInputParams));

		return $this;
	}

	/**
	 * Emulate PDO fetch public function
	 *
	 * @param int $fetchStyle
	 * @param mixed $cursorOrientation
	 * @param mixed $cursorOffset
	 * @return mixed
	 */
	public function fetch($fetchStyle=\PDO::FETCH_ASSOC, $cursorOrientation = \PDO::FETCH_ORI_NEXT, $cursorOffset=NULL)
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

		switch($fetchStyle)
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

	/**
	 * Emulate PDO fetchAll public function
	 *
	 * @param int  $fetchStyle
	 * @param mixed $statement
	 * @param mixed $ctorArgs
	 * @return mixed
	 */
	public function fetchAll($fetchStyle=\PDO::FETCH_ASSOC, $statement=NULL, $ctorArgs=NULL)
	{
		$all = [];

		while($row = $this->fetch($fetchStyle, $statement))
		{
			$all[] = $row;
		}

		$this->result = $all;

		return $all;
	}

	/**
	 * Emulate PDOStatement::fetchColumn
	 *
	 * @param int $columnNum
	 * @return mixed
	 */
	public function fetchColumn($columnNum=0)
	{
		$row = $this->fetch(\PDO::FETCH_NUM);
		return $row[$columnNum];
	}

	/**
	 * Emulate PDOStatement::fetchObject, but only for the default use
	 *
	 * @param string $className
	 * @param array|null $ctorArgs
	 * @return object
	 */
	public function fetchObject($className='stdClass', $ctorArgs=NULL)
	{
		return $this->fetch(\PDO::FETCH_OBJ);
	}

	/**
	 * Return the number of rows affected by the previous query
	 *
	 * @return int
	 */
	public function rowCount()
	{
		return \fbird_affected_rows();
	}

	/**
	 * Method to emulate PDOStatement->errorCode
	 *
	 * @return string
	 */
	public function errorCode()
	{
		return $this->db->errorCode();
	}

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