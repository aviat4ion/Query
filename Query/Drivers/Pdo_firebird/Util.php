<?php
/**
 * Query
 *
 * Free Query Builder / Database Abstraction Layer
 *
 * @package		Query
 * @author		Timothy J. Warren
 * @copyright	Copyright (c) 2012 - 2014
 * @link 		https://github.com/aviat4ion/Query
 * @license		http://philsturgeon.co.uk/code/dbad-license
 */

// --------------------------------------------------------------------------

namespace Query\Drivers\Pdo_firebird;

/**
 * Firebird-specific backup, import and creation methods
 *
 * @package Query
 * @subpackage Drivers
 */
class Util extends \Query\Drivers\Firebird\Util {

	/**
	 * Create an SQL backup file for the current database's structure
	 * @codeCoverageIgnore
	 * @param string $db_path
	 * @param string $new_file
	 * @return string
	 */
	public function backup_structure()
	{
		return NULL;
	}
}
// End of firebird_util.php