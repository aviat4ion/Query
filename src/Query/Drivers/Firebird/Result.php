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

namespace Query\Drivers\Firebird;

/**
 * Firebird result class to emulate PDOStatement Class - only implements
 * data-fetching methods
 *
 * @package Query
 * @subpackage Drivers
 */
class Result extends \PDOStatement {

	/**
	 * Reference to fbird resource
	 *
	 * @var resource
	 */
	private $statement;

	/**
	 * Current row in result array
	 *
	 * @var int
	 */
	private $row;

	/**
	 * Data pulled from query
	 *
	 * @param mixed
	 */
	private $result = array();

	/**
	 * Reference to the db drive to de-duplicate error functions
	 *
	 * @var \Query\Drivers\Firebird\Driver
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
		if ( ! is_null($db)) $this->db = $db;
		$this->statement = $link;
		$this->setFetchMode(\PDO::FETCH_ASSOC);
		$this->row = -1;
		$this->result = array();

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
	 * @param  array $args
	 * @return Result
	 */
	public function execute($args = NULL)
	{
		//Add the prepared statement as the first parameter
		\array_unshift($args, $this->statement);

		// Let php do all the hard stuff in converting
		// the array of arguments into a list of arguments
		// Then pass the resource to the constructor
		$this->__construct(\call_user_func_array('fbird_execute', $args));

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
		$all = array();

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
	 * @param array $ctor_args
	 * @return stdClass
	 */
	public function fetchObject($class_name='stdClass', $ctor_args=array())
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
		$rows = \fbird_affected_rows();

		// Get the number of rows for the select query if you can
		if ($rows === 0 && \is_resource($this->statement) && \get_resource_type($this->statement) === "interbase result")
		{
		// @codeCoverageIgnoreStart
			$rows = \count($this->result);
		}
		// @codeCoverageIgnoreEnd

		return $rows;
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