<?php declare(strict_types=1);
/**
 * Query
 *
 * SQL Query Builder / Database Abstraction Layer
 *
 * PHP version 7.2
 *
 * @package     Query
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2012 - 2020 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link        https://git.timshomepage.net/aviat/Query
 * @version     3.0.0
 */
namespace Query\Drivers\Mysql;

use PDO;
use Query\Drivers\AbstractDriver;

/**
 * MySQL specific class
 */
class Driver extends AbstractDriver {

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
	public function __construct(string $dsn, string $username=NULL, string $password=NULL, array $options=[])
	{
		// Set the charset to UTF-8
		if (\defined('\\PDO::MYSQL_ATTR_INIT_COMMAND'))
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

	/**
	 * Generate the returning clause for the current database
	 *
	 * @param string $query
	 * @param string $select
	 * @return string
	 */
	public function returning(string $query, string $select): string
	{
		// @TODO add checks for MariaDB for future-proofing
		// MariaDB 10.5.0+ supports the returning clause for insert
		if (
			stripos($query, 'insert') !== FALSE
			&& version_compare($this->getVersion(), '10.5.0', '>=')
		){
			return parent::returning($query, $select);
		}

		// MariaDB 10.0.5+ supports the returning clause for delete
		if (
			stripos($query, 'delete') !== FALSE
			&& version_compare($this->getVersion(), '10.0.5', '>=')
		){
			return parent::returning($query, $select);
		}

		// Just return the same SQL if the returning clause is not supported
		return $query;
	}
}