<?php declare(strict_types=1);
/**
 * Query
 *
 * SQL Query Builder / Database Abstraction Layer
 *
 * PHP version 7.2
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
	 * TestPHPVersion function.
	 *
	 * @access public
	 * @return void
	 */
	public function testPHPVersion(): void
	{
		$this->assertTrue(PHP_VERSION_ID >= 70100);
		$this->assertTrue(PHP_MAJOR_VERSION >= 7);
		$this->assertTrue(PHP_MINOR_VERSION >= 1);
	}

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