<?php declare(strict_types=1);
/**
 * Query
 *
 * SQL Query Builder / Database Abstraction Layer
 *
 * PHP version 7.4
 *
 * @package     Query
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2012 - 2020 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link        https://git.timshomepage.net/aviat/Query
 * @version     3.0.0
 */
namespace Query;

/**
 * 'Enum' of query types
 */
class QueryType {
	public final const SELECT = 'select';
	public final const INSERT = 'insert';
	public final const INSERT_BATCH = 'insert_batch';
	public final const UPDATE = 'update';
	public final const UPDATE_BATCH = 'update_batch';
	public final const DELETE = 'delete';
}