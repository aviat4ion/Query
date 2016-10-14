<?php
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

/**
 * Quercus detection for workarounds
 */
if ( ! defined('IS_QUERCUS'))
{
	if ( ! array_key_exists('SERVER_SOFTWARE', $_SERVER))
	{
		define('IS_QUERCUS', FALSE);
	}
	else
	{
		$test = strpos($_SERVER["SERVER_SOFTWARE"],'Quercus') !== FALSE;
		define('IS_QUERCUS', $test);
		unset($test);
	}
}

function get_json_config()
{
	$files = array(
		__DIR__ . '/settings.json',
		__DIR__ . '/settings.json.dist'
	);

	foreach($files as $file)
	{
		if (is_file($file))
		{
			return json_decode(file_get_contents($file));
		}
	}

	return FALSE;
}

// --------------------------------------------------------------------------

// Set up autoloaders
require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/../vendor/simpletest/simpletest/autorun.php');

/**
 * Base class for TestCases
 */
abstract class Query_TestCase extends UnitTestCase {

	public function __construct()
	{
		$class = get_class($this);

		if (method_exists($class, 'setupBeforeClass'))
		{
			$class::setupBeforeClass();
		}

		parent::__construct();
	}

	public function __destruct()
	{
		$class = get_class($this);

		if (method_exists($class, 'tearDownAfterClass'))
		{
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

	/**
	 * Alias for phpunit method
	 *
	 * @param string $name
	 * @param string $message
	 * @param int $code
	 */
	public function setExpectedException($name, $message='', $code=NULL)
	{
		$this->expectException($name, $message);
	}
}

// --------------------------------------------------------------------------

/**
 * Unit test bootstrap - Using php simpletest
 */
define('QTEST_DIR', __DIR__);
define('QBASE_DIR', realpath(__DIR__ . '/../') . '/');
define('QDS', DIRECTORY_SEPARATOR);

// Include db tests
// Load db classes based on capability
$testPath = QTEST_DIR.'/databases/';

// Require base testing classes
require_once(QTEST_DIR . '/core/core_test.php');
require_once(QTEST_DIR . '/core/connection_manager_test.php');
require_once(QTEST_DIR . '/core/base_db_test.php');
require_once(QTEST_DIR . '/core/query_parser_test.php');
require_once(QTEST_DIR . '/core/base_query_builder_test.php');

$drivers = PDO::getAvailableDrivers();

if (function_exists('fbird_connect'))
{
	$drivers[] = 'interbase';
}

$driverTestMap = array(
	'MySQL' => in_array('mysql', $drivers),
	'SQLite' => in_array('sqlite', $drivers),
	'PgSQL' => in_array('pgsql', $drivers),
	'Firebird' => in_array('interbase', $drivers),
	//'PDOFirebird' => in_array('firebird', $drivers)
);

// Determine which testcases to load
foreach($driverTestMap as $name => $doLoad)
{
	$path = $testPath . strtolower($name) . '/';

	if ($doLoad && (! IS_QUERCUS))
	{
		require_once("{$path}{$name}Test.php");
		require_once("{$path}{$name}QBTest.php");
	}
}
// End of index.php