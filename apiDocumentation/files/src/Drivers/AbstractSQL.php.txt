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

namespace Query\Drivers;

/**
 * Parent for database-specific syntax subclasses
 */
abstract class AbstractSQL implements SQLInterface
{
	/**
	 * Limit clause
	 */
	public function limit(string $sql, int $limit, ?int $offset=NULL): string
	{
		$sql .= "\nLIMIT {$limit}";

		if (is_numeric($offset))
		{
			$sql .= " OFFSET {$offset}";
		}

		return $sql;
	}
}
