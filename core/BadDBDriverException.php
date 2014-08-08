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

namespace Query;

/**
 * Generic exception for bad drivers
 *
 * @package Query
 * @subpackage Core
 */
class BadDBDriverException extends \InvalidArgumentException {}

// End of BadDBDriverException.php