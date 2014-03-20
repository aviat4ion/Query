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

require_once realpath(__DIR__ . '/../firebird/firebird_util.php');

/**
 * Firebird-specific backup, import and creation methods
 *
 * @package Query
 * @subpackage Drivers
 */
class PDO_Firebird_Util extends Firebird_Util {}
// End of pdo_firebird_util.php