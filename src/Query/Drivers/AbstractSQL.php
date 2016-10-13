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




// --------------------------------------------------------------------------

namespace Query\Drivers;

/**
 * parent for database manipulation subclasses
 *
 * @package Query
 * @subpackage Drivers
 */
abstract class AbstractSQL implements SQLInterface {

	/**
	 * Limit clause
	 *
	 * @param string $sql
	 * @param int $limit
	 * @param int|bool $offset
	 * @return string
	 */
	public function limit($sql, $limit, $offset=FALSE)
	{
		$sql .= "\nLIMIT {$limit}";

		if (is_numeric($offset))
		{
			$sql .= " OFFSET {$offset}";
		}

		return $sql;
	}
}
// End of abstract_sql.php
