<?php
/**
 * OpenSQLManager
 *
 * Free Database manager for Open Source Databases
 *
 * @author 		Timothy J. Warren
 * @copyright	Copyright (c) 2012
 * @link 		https://github.com/aviat4ion/OpenSQLManager
 * @license 	http://philsturgeon.co.uk/code/dbad-license
 */

// --------------------------------------------------------------------------

/**
 * Class for manipulating datbase connections, and program settings
 *
 * Use JSON for compatibility
 */
class Settings {

	private $current;
	private static $instance;

	public static function &get_instance()
	{
		if( ! isset(self::$instance))
		{
			$name = __CLASS__;
			self::$instance = new $name();
		}

		return self::$instance;
	}

	/**
	 * Load the settings file - private so it can't be loaded
	 * directly - the settings should be safe!
	 */
	private function __construct()
	{
		if ( ! defined('SETTINGS_DIR'))
		{
			define('SETTINGS_DIR', '.');
		}

		$path = SETTINGS_DIR.'/settings.json';

		if( ! is_file($path))
		{
			//Create the file!
			touch($path);
			$this->current = new stdClass();
		}
		else
		{
			$this->current = json_decode(file_get_contents($path));
		}

		// Add the DB object under the settings if it doesn't already exist
		if( ! isset($this->current->dbs))
		{
			$this->current->dbs = new stdClass();
		}

	}

	// --------------------------------------------------------------------------

	/**
	 * Output the settings on destruct
	 */
	public function __destruct()
	{
		$file_string = (defined('JSON_PRETTY_PRINT'))
			? json_encode($this->current, JSON_PRETTY_PRINT)
			: json_encode($this->current);

		file_put_contents(SETTINGS_DIR . '/settings.json', $file_string);
	}

	// --------------------------------------------------------------------------

	/**
	 * Magic method to simplify isset checking for config options
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __get($key)
	{
		return (isset($this->current->{$key}) && $key != "dbs")
			? $this->current->{$key}
			: NULL;
	}

	// --------------------------------------------------------------------------

	/**
	 * Magic method to simplify setting config options
	 *
	 * @param string $key
	 * @param mixed
	 */
	public function __set($key, $val)
	{
		//Don't allow direct db config changes
		if($key == "dbs")
		{
			return FALSE;
		}

		return $this->current->{$key} = $val;
	}

	// --------------------------------------------------------------------------

	/**
	 * Add a database connection
	 *
	 * @param string $name
	 * @param array $params
	 */
	public function add_db($name, $params)
	{
		// Return on bad data
		if (empty($name) || empty($params))
		{
			return FALSE;
		}

		if( ! isset($this->current->dbs->{$name}))
		{
			$params['name'] = $name;

			$this->current->dbs->{$name} = array();
			$this->current->dbs->{$name} = $params;
		}
		else
		{
			return FALSE;
		}

		// Save the json
		$this->__destruct();
	}

	// --------------------------------------------------------------------------

	/**
	 * Edit a database connection
	 *
	 * @param array $params
	 */
	public function edit_db($name, $params)
	{
		// Return on bad data
		if (empty($name) || empty($params))
		{
			return FALSE;
		}
		
		if (isset($this->current->dbs->{$name}) && ($name === $params['name']))
		{
			$this->current->dbs->{$name} = $params;
		}
		elseif ($name !== $params['name'])
		{
			unset($this->current->dbs->{$name});

			if ( ! isset($this->current->dbs->{$params['name']}))
			{
				$this->current->dbs->{$params['name']} = $params;
			}
			else
			{
				return FALSE;
			}
		}
		else
		{
			return FALSE;
		}

		// Save the json
		$this->__destruct();

		return TRUE;
	}

	// --------------------------------------------------------------------------

	/**
	 * Remove a database connection
	 *
	 * @param  string $name
	 */
	public function remove_db($name)
	{
		if( ! isset($this->current->dbs->{$name}))
		{
			return FALSE;
		}

		// Remove the db name from the object
		unset($this->current->dbs->{$name});

		// Save the json
		$this->__destruct();
	}

	// --------------------------------------------------------------------------

	/**
	 * Retreive all db connections
	 *
	 * @return  array
	 */
	public function get_dbs()
	{
		return $this->current->dbs;
	}

	// --------------------------------------------------------------------------

	/**
	 * Retreive a specific database connection
	 *
	 * @param string $name
	 * @return object
	 */
	public function get_db($name)
	{
		return (isset($this->current->dbs->{$name}))
			? $this->current->dbs->{$name}
			: FALSE;
	}

}
// End of settings.php