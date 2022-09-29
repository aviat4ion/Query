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
 * 'Enum' of query map types
 */
class MapType {
	public final const GROUP_END = 'group_end';
	public final const GROUP_START = 'group_start';
	public final const JOIN = 'join';
	public final const LIKE = 'like';
	public final const WHERE = 'where';
	public final const WHERE_IN = 'where_in';
}