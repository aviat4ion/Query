<?php
/**
 * Query
 *
 * Free Query Builder / Database Abstraction Layer
 *
 * @author 		Timothy J. Warren
 * @copyright	Copyright (c) 2012
 * @link 		https://github.com/aviat4ion/Query
 * @license 	http://philsturgeon.co.uk/code/dbad-license 
 */

// --------------------------------------------------------------------------

/**
 * Firebird result class to emulate PDOStatement Class - only implements
 * data-fetching methods
 *
 * @todo Implement more of the PDOStatement Class
 */
class Firebird_Result {

	private $statement;

	/**
	 * Create the object by passing the resource for
	 * the query
	 *
	 * @param resource $link
	 */
	public function __construct($link)
	{
		$this->statement = $link;
	}

	// --------------------------------------------------------------------------

	/**
	 * Emulate PDO fetch public function
	 *
	 * @param  int $fetch_style
	 * @return mixed
	 */
	public function fetch($fetch_style=PDO::FETCH_ASSOC, $statement=NULL)
	{
		if ( ! is_null($statement))
		{
			$this->statement = $statement;
		}

		switch($fetch_style)
		{
			case PDO::FETCH_OBJ:
				return fbird_fetch_object($this->statement, IBASE_FETCH_BLOBS);
			break;

			case PDO::FETCH_NUM:
				return fbird_fetch_row($this->statement, IBASE_FETCH_BLOBS);
			break;

			default:
				return fbird_fetch_assoc($this->statement, IBASE_FETCH_BLOBS);
			break;
		}
	}

	// --------------------------------------------------------------------------

	/**
	 * Emulate PDO fetchAll public function
	 *
	 * @param  int  $fetch_style
	 * @return mixed
	 */
	public function fetchAll($fetch_style=PDO::FETCH_ASSOC, $statement=NULL)
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
	 * Run a prepared statement query
	 *
	 * @param  array $args
	 * @return bool
	 */
	public function execute($args)
	{
		//Add the prepared statement as the first parameter
		array_unshift($args, $this->statement);

		// Let php do all the hard stuff in converting
		// the array of arguments into a list of arguments
		// Then pass the resource to the constructor
		$this->__construct(call_user_func_array('fbird_execute', $args));

		return $this;
	}

	// --------------------------------------------------------------------------

	/**
	 * Return the number of rows affected by the previous query
	 *
	 * @return int
	 */
	public function rowCount()
	{
		return fbird_affected_rows();
	}

	// --------------------------------------------------------------------------

	/**
	 * Method to emulate PDO->errorInfo / PDOStatement->errorInfo
	 *
	 * @return array
	 */
	public function errorInfo()
	{
		$code = fbird_errcode();
		$msg = fbird_errmsg();

		return array(0, $code, $msg);
	}
}
// End of firebird_result.php