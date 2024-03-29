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
 * @version     4.1.0
 */

namespace Query;

/**
 * Enum of query types
 */
enum QueryType: string
{
	case SELECT = 'select';
	case INSERT = 'insert';
	case INSERT_BATCH = 'insert_batch';
	case UPDATE = 'update';
	case UPDATE_BATCH = 'update_batch';
	case DELETE = 'delete';
}
