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
 * Connection registry
 *
 * Decouples the Settings class from the query builder
 * and organizes database connections
 *
 * @package Query
 * @subpackage Helper Classes
 */
class DB_Reg {

	/**
	 * Static array of connections
	 */
	private static $instance=array();

	/**
	 * Registry access method
	 *
	 * @param string $key
	 * @return object
	 */
	public static function &get_db($key)
	{
		if ( ! isset(self::$instance[$key]))
		{
			// The constructor sets the instance
			new DB_Reg($key);
		}

		return self::$instance[$key];
	}

	// --------------------------------------------------------------------------

	/**
	 * Private constructor
	 *
	 * @param string $key
	 */
	private function __construct($key)
	{
		// Get the db connection parameters for the current database
		$db_params = Settings::get_instance()->get_db($key);

		// Set the current key in the registry
		self::$instance[$key] = new Query_Builder($db_params);
	}

	// --------------------------------------------------------------------------

	/**
	 * Return exiting connections
	 *
	 * @return array
	 */
	public static function get_connections()
	{
		return array_keys(self::$instance);
	}

	// --------------------------------------------------------------------------

	/**
	 * Remove a database connection
	 *
	 * @param string $key
	 * @return void
	 */
	public static function remove_db($key)
	{
		unset(self::$instance[$key]);
	}
}
// End of dbreg.php