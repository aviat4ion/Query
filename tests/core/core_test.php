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


// --------------------------------------------------------------------------

/**
 * CoreTest class - Compatibility and core functionality tests
 *
 * @extends UnitTestCase
 */
class CoreTest extends Query_TestCase {

	/**
	 * TestPHPVersion function.
	 *
	 * @access public
	 * @return void
	 */
	public function testPHPVersion()
	{
		$this->assertTrue(version_compare(PHP_VERSION, "5.3", "ge"));
	}

	// --------------------------------------------------------------------------

	/**
	 * TestHasPDO function.
	 *
	 * @access public
	 * @return void
	 */
	public function testHasPDO()
	{
		// PDO class exists
		$this->assertTrue(class_exists('PDO'));


		// Make sure at least one of the supported drivers is enabled
		$supported = array(
			'firebird',
			'mysql',
			'pgsql',
			'odbc',
			'sqlite',
		);

		$drivers = PDO::getAvailableDrivers();

		$num_supported = count(array_intersect($drivers, $supported));

		$this->assertTrue($num_supported > 0);
	}
}