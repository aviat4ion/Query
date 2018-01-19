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
namespace Query\Drivers\Mysql;

use PDO;
use Query\Drivers\AbstractDriver;
use Query\Drivers\DriverInterface;

/**
 * MySQL specific class
 */
class Driver extends AbstractDriver implements DriverInterface {

	/**
	 * Set the backtick as the MySQL escape character
	 *
	 * @var string
	 */
	protected $escapeCharOpen = '`';

	/**
	 * Set the backtick as the MySQL escape character
	 *
	 * @var string
	 */
	protected $escapeCharClose = '`';

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
				PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF-8 COLLATE 'UTF-8'",
			]);
		}

		if (strpos($dsn, 'mysql') === FALSE)
		{
			$dsn = 'mysql:'.$dsn;
		}

		parent::__construct($dsn, $username, $password, $options);
	}
}