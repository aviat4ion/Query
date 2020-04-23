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
 * 'Enum' of join types
 */
class JoinType {
	public const CROSS = 'cross';
	public const INNER = 'inner';
	public const OUTER = 'outer';
	public const LEFT = 'left';
	public const RIGHT = 'right';
}