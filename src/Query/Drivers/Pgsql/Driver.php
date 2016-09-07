<?php
/**
 * Query
 *
 * SQL Query Builder / Database Abstraction Layer
 *
 * PHP version 5.4
 *
 * @package     Query
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2012 - 2015 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link        https://git.timshomepage.net/aviat4ion/Query
 */

namespace Query\Drivers\Pgsql;

use Query\Drivers\AbstractDriver;

/**
 * PostgreSQL specific class
 *
 * @package Query
 * @subpackage Drivers
 */
class Driver extends AbstractDriver {

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
	public function get_schemas()
	{
		$sql = <<<SQL
			SELECT DISTINCT "schemaname" FROM "pg_tables"
			WHERE "schemaname" NOT LIKE 'pg\_%'
			AND "schemaname" != 'information_schema'
SQL;

		return $this->driver_query($sql);
	}

	// --------------------------------------------------------------------------

	/**
	 * Retrieve foreign keys for the table
	 *
	 * @param string $table
	 * @return array
	 */
	public function get_fks($table)
	{
		$value_map = [
			'c' => 'CASCADE',
			'r' => 'RESTRICT',
		];

		$keys = parent::get_fks($table);

		foreach($keys as &$key)
		{
			foreach(['update', 'delete'] AS $type)
			{
				if ( ! isset($value_map[$key[$type]]))
				{
					continue;
				}

				$key[$type] = $value_map[$key[$type]];
			}
		}

		return $keys;
	}
}
//End of pgsql_driver.php