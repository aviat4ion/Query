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
namespace Query\Tests;

use function Query;
use function regexInArray;
use PDO;

/**
 * CoreTest class - Compatibility and core functionality tests
 *
 * @extends UnitTestCase
 */
class CoreTest extends TestCase {
	/**
	 * TestHasPDO function.
	 *
	 * @access public
	 * @return void
	 */
	public function testHasPDO(): void
	{
		// PDO class exists
		$this->assertTrue(class_exists('PDO'));


		// Make sure at least one of the supported drivers is enabled
		$supported = [
			'mysql',
			'pgsql',
			'sqlite',
		];

		$drivers = PDO::getAvailableDrivers();

		$numSupported = count(array_intersect($drivers, $supported));

		$this->assertTrue($numSupported > 0);
	}

	public function testNullQuery(): void
	{
		$this->assertNull(Query(NULL));
	}

	public function testEmptyRegexInArray(): void
	{
		$this->assertFalse(regexInArray([], 'foo'));
	}
}