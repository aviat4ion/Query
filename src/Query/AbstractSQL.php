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


// --------------------------------------------------------------------------

namespace Query;

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
