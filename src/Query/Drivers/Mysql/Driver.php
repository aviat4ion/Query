<?php
/**
 * Query
 *
 * Free Query Builder / Database Abstraction Layer
 *
 * @package		Query
 * @author		Timothy J. Warren
 * @copyright	Copyright (c) 2012 - 2015
 * @link 		https://github.com/aviat4ion/Query
 * @license		http://philsturgeon.co.uk/code/dbad-license
 */

// --------------------------------------------------------------------------

namespace Query\Drivers\Mysql;

/**
 * MySQL specific class
 *
 * @package Query
 * @subpackage Drivers
 */
class Driver extends \Query\AbstractDriver {

	/**
	 * Set the backtick as the MySQL escape character
	 *
	 * @var string
	 */
	protected $escape_char = '`';

	/**
	 * Connect to MySQL Database
	 *
	 * @codeCoverageIgnore
	 * @param string $dsn
	 * @param string $username
	 * @param string $password
	 * @param array $options
	 */
	public function __construct($dsn, $username=NULL, $password=NULL, array $options=[])
	{
		// Set the charset to UTF-8
		if (defined('\\PDO::MYSQL_ATTR_INIT_COMMAND'))
		{
			$options = array_merge($options, [
				\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF-8 COLLATE 'UTF-8'",
			]);
		}

		if (strpos($dsn, 'mysql') === FALSE)
		{
			$dsn = 'mysql:'.$dsn;
		}

		parent::__construct($dsn, $username, $password, $options);
	}
}
//End of mysql_driver.php