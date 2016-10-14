<?php declare(strict_types=1);
/**
 * Query
 *
 * SQL Query Builder / Database Abstraction Layer
 *
 * PHP version 7
 *
 * @package     Query
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2012 - 2016 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link        https://git.timshomepage.net/aviat4ion/Query
 */
namespace Query\Drivers\Pgsql;

use Query\Drivers\AbstractDriver;
use Query\Drivers\DriverInterface;

/**
 * PostgreSQL specific class
 */
class Driver extends AbstractDriver implements DriverInterface {

	/**
	 * Connect to a PosgreSQL database
	 *
	 * @codeCoverageIgnore
	 * @param string $dsn
	 * @param string $username
	 * @param string $password
	 * @param array  $options
	 */
	public function __construct($dsn, $username=NULL, $password=NULL, array $options=[])
	{
		if (strpos($dsn, 'pgsql') === FALSE)
		{
			$dsn = 'pgsql:'.$dsn;
		}

		parent::__construct($dsn, $username, $password, $options);
	}

	// --------------------------------------------------------------------------

	/**
	 * Get a list of schemas for the current connection
	 *
	 * @return array
	 */
	public function getSchemas()
	{
		$sql = <<<SQL
			SELECT DISTINCT "schemaname" FROM "pg_tables"
			WHERE "schemaname" NOT LIKE 'pg\_%'
			AND "schemaname" != 'information_schema'
SQL;

		return $this->driverQuery($sql);
	}

	// --------------------------------------------------------------------------

	/**
	 * Retrieve foreign keys for the table
	 *
	 * @param string $table
	 * @return array
	 */
	public function getFks($table)
	{
		$valueMap = [
			'c' => 'CASCADE',
			'r' => 'RESTRICT',
		];

		$keys = parent::getFks($table);

		foreach($keys as &$key)
		{
			foreach(['update', 'delete'] AS $type)
			{
				if ( ! isset($valueMap[$key[$type]]))
				{
					continue;
				}

				$key[$type] = $valueMap[$key[$type]];
			}
		}

		return $keys;
	}
}