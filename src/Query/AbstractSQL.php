<?php
/**
 * Query
 *
 * Free Query Builder / Database Abstraction Layer
 *
 * @author		Timothy J. Warren
 * @copyright	Copyright (c) 2012 - 2014
 * @link 		https://github.com/aviat4ion/Query
 * @license		http://philsturgeon.co.uk/code/dbad-license
 * @package		Query
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
