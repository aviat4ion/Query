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
 * @version     4.0.0
 */
namespace Query;

/**
 * Enum of join types
 */
enum JoinType: string {
	case CROSS = 'cross';
	case INNER = 'inner';
	case OUTER = 'outer';
	case LEFT = 'left';
	case RIGHT = 'right';

	public static function parse(string|self $val): self
	{
		if ($val instanceof self)
		{
			return $val;
		}

		return self::from($val);
	}
}

