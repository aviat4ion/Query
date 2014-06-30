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
	if ( ! isset($_SERVER_SOFTWARE))
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

// --------------------------------------------------------------------------

// Include simpletest
// it has to be in the tests folder
require_once('/htdocs/__lib/simpletest/autorun.php');

/**
 * Base class for TestCases
 */
abstract class Query_TestCase extends UnitTestCase {

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
}

// --------------------------------------------------------------------------

/**
 * Unit test bootstrap - Using php simpletest
 */
define('QTEST_DIR', __DIR__);
define('QBASE_DIR', realpath(__DIR__ . '/../') . '/');
define('QDS', DIRECTORY_SEPARATOR);

// Include db classes
require_once(QBASE_DIR . 'autoload.php');

// Preset SQLite connection, so there aren't locking issues
$params = array(
	'type' => 'sqlite',
	'file' => ':memory:',
	'host' => 'localhost',
	'prefix' => 'create_',
	'alias' => 'test_sqlite',
	'options' => array(
		PDO::ATTR_PERSISTENT => TRUE
	)
);

Query($params);
unset($params);

// Include db tests
// Load db classes based on capability
$test_path = QTEST_DIR.'/databases/';

// Require base testing classes
require_once(QTEST_DIR . '/core/core.php');
require_once(QTEST_DIR . '/core/db_test.php');
require_once(QTEST_DIR . '/core/db_qb_test.php');

$drivers = PDO::getAvailableDrivers();

if (function_exists('fbird_connect'))
{
	$drivers[] = 'interbase';
}

$driver_test_map = array(
	'Firebird' => in_array('interbase', $drivers),
	'SQLite' => in_array('sqlite', $drivers),
	'PgSQL' => in_array('pgsql', $drivers),
);

// Determine which testcases to load
foreach($driver_test_map as $name => $do_load)
{
	$path = $test_path . strtolower($name) . '/';

	if ($do_load && ($name != 'SQLite' && ! IS_QUERCUS))
	{
		require_once("{$path}{$name}Test.php");
		require_once("{$path}{$name}QBTest.php");
	}
}
// End of index.php