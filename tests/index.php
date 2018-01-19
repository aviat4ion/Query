<?php declare(strict_types=1);
/**
 * Query
 *
 * Free Query Builder / Database Abstraction Layer
 *
 * @package		Query
 * @author		Timothy J. Warren
 * @copyright	Copyright (c) 2012 - 2013
 * @link 		https://github.com/aviat4ion/Query
 * @license		http://philsturgeon.co.uk/code/dbad-license
 */

namespace {
	/**
	 * Unit test bootstrap - Using php simpletest
	 */
	\define('QTEST_DIR', __DIR__);
	\define('QBASE_DIR', realpath(__DIR__ . '/../') . '/');
	\define('QDS', DIRECTORY_SEPARATOR);

	require_once QBASE_DIR . 'vendor/simpletest/simpletest/autorun.php';
	require_once QBASE_DIR . 'vendor/autoload.php';
}

namespace Query\Tests {

	/**
	 * Base class for TestCases
	 */
	abstract class TestCase extends \UnitTestCase
	{

		public function __construct()
		{
			$class = \get_class($this);

			echo 'Ran test suite: ' . $class . '<br />';

			if (method_exists($class, 'setupBeforeClass')) {
				$class::setupBeforeClass();
			}

			parent::__construct();
		}

		public function __destruct()
		{
			$class = \get_class($this);

			if (method_exists($class, 'tearDownAfterClass')) {
				$class::tearDownAfterClass();
			}
		}

		/**
		 * Define assertInstanceOf for simpletest
		 *
		 * @param $expected
		 * @param $actual
		 * @param string $message
		 */
		public function assertInstanceOf($expected, $actual, $message = '')
		{
			$this->assertIsA($actual, $expected, $message);
		}

		/**
		 * Alias to assertEqual
		 *
		 * @param mixed $expected
		 * @param mixed $actual
		 * @param string $message
		 */
		public function assertEquals($expected, $actual, $message = '')
		{
			$this->assertEqual($expected, $actual, $message);
		}

		/**
		 * Alias to skipIf in SimpleTest
		 *
		 * @param string $message
		 */
		public function markTestSkipped($message = '')
		{
			$this->skipUnless(FALSE, $message);
		}

		/**
		 * Alias to the method in PHPUnit
		 *
		 * @param string $message
		 */
		public function expectExceptionMessage($message)
		{
			// noop
		}
	}

}

/**
 * Load the test suites
 */
namespace {
	function get_json_config()
	{
		$files = array(
			__DIR__ . '/settings.json',
			__DIR__ . '/settings.json.dist'
		);

		foreach ($files as $file) {
			if (is_file($file)) {
				return json_decode(file_get_contents($file));
			}
		}

		return FALSE;
	}

	// Include db tests
	// Load db classes based on capability
	$testPath = QTEST_DIR.'/Drivers/';

	// Require base testing classes
	require_once QTEST_DIR . '/CoreTest.php';
	require_once QTEST_DIR . '/ConnectionManagerTest.php';
	require_once QTEST_DIR . '/QueryParserTest.php';

	$drivers = PDO::getAvailableDrivers();

	/* if (function_exists('fbird_connect'))
	{
		$drivers[] = 'interbase';
	} */

	$driverTestMap = [
		'MySQL' => \in_array('mysql', $drivers, TRUE),
		'SQLite' => \in_array('sqlite', $drivers, TRUE),
		'PgSQL' => \in_array('pgsql', $drivers, TRUE),
		// 'Firebird' => in_array('interbase', $drivers),
		//'PDOFirebird' => in_array('firebird', $drivers)
	];

	// Determine which testcases to load
	foreach($driverTestMap as $name => $doLoad)
	{
		$path = $testPath . $name;

		if ($doLoad)
		{
			require_once "{$path}/{$name}DriverTest.php";
			require_once "{$path}/{$name}QueryBuilderTest.php";
		}
	}
}


// End of index.php
