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

namespace Query\Drivers\Pdo_firebird;

/**
 * Firebird specific class
 *
 * @package Query
 * @subpackage Drivers
 */
class Driver extends \Query\Abstract_Driver {

	/**
	 * Firebird doesn't have the truncate keyword
	 *
	 * @var bool
	 */
	protected $has_truncate = FALSE;

	/**
	 * Connect to Firebird Database
	 *
	 * @param string $dsn
	 * @param string $username
	 * @param string $password
	 * @param array $options
	 */
	public function __construct($dsn, $username="SYSDBA", $password="masterkey", array $options=array())
	{
		parent::__construct($dsn, $username, $password, $options);
	}

		// --------------------------------------------------------------------------

	/**
	 * Create sql for batch insert
	 *
	 * @param string $table
	 * @param array $data
	 * @return array
	 */
	public function insert_batch($table, $data=array())
	{
		// Each member of the data array needs to be an array
		if ( ! is_array(current($data))) return NULL;

		// Start the block of sql statements
		$sql = "EXECUTE BLOCK AS BEGIN\n";

		$table = $this->quote_table($table);
		$fields = \array_keys(\current($data));

		$insert_template = "INSERT INTO {$table} ("
			. implode(',', $this->quote_ident($fields))
			. ") VALUES (";

		foreach($data as $item)
		{
			// Quote string values
			$vals = array_map(array($this, 'quote'), $item);

			// Add the values in the sql
			$sql .= $insert_template . implode(', ', $vals) . ");\n";
		}

		// End the block of SQL statements
		$sql .= "END";

		// Return a null array value so the query is run as it is,
		// not as a prepared statement, because a prepared statement
		// doesn't work for this type of query in Firebird.
		return array($sql, NULL);
	}
}
//End of firebird_driver.php