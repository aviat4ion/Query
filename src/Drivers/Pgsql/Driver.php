<?php declare(strict_types=1);
/**
 * Query
 *
 * SQL Query Builder / Database Abstraction Layer
 *
 * PHP version 8.1
 *
 * @package     Query
 * @author      Timothy J. Warren <tim@timshome.page>
 * @copyright   2012 - 2023 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link        https://git.timshomepage.net/aviat/Query
 * @version     4.0.0
 */

namespace Query\Drivers\Pgsql;

use Query\Drivers\AbstractDriver;

/**
 * PostgreSQL specific class
 */
class Driver extends AbstractDriver
{
	/**
	 * Connect to a PosgreSQL database
	 *
	 * @codeCoverageIgnore
	 */
	public function __construct(string $dsn, ?string $username=NULL, ?string $password=NULL, array $options=[])
	{
		if ( ! str_contains($dsn, 'pgsql'))
		{
			$dsn = 'pgsql:' . $dsn;
		}

		parent::__construct($dsn, $username, $password, $options);
	}

	/**
	 * Get a list of schemas for the current connection
	 */
	public function getSchemas(): ?array
	{
		$sql = <<<SQL
			SELECT DISTINCT "schemaname" FROM "pg_tables"
			WHERE "schemaname" NOT LIKE 'pg\_%'
			AND "schemaname" != 'information_schema'
SQL;

		return $this->driverQuery($sql);
	}

	/**
	 * Retrieve foreign keys for the table
	 */
	public function getFks(string $table): array
	{
		$valueMap = [
			'c' => 'CASCADE',
			'r' => 'RESTRICT',
		];

		$keys = parent::getFks($table);

		foreach ($keys as &$key)
		{
			foreach (['update', 'delete'] as $type)
			{
				if ( ! isset($valueMap[$key[$type]]))
				{
					// @codeCoverageIgnoreStart
					continue;
					// @codeCoverageIgnoreEnd
				}

				$key[$type] = $valueMap[$key[$type]];
			}
		}

		return $keys;
	}
}
