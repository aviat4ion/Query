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



namespace Query;

use InvalidArgumentException;

/**
 * Generic exception for bad drivers
 *
 * @package Query
 * @subpackage Core
 */
class BadDBDriverException extends InvalidArgumentException {
}

// End of BadDBDriverException.php