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
	public const GROUP_END = 'group_end';
	public const GROUP_START = 'group_start';
	public const JOIN = 'join';
	public const LIKE = 'like';
	public const WHERE = 'where';
	public const WHERE_IN = 'where_in';
}