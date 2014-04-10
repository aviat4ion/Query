<?php
/**
 * Query
 *
 * Free Query Builder / Database Abstraction Layer
 *
 * @package		Query
 * @author		Timothy J. Warren
 * @copyright	Copyright (c) 2012 - 2014
 * @link 		https://github.com/aviat4ion/Query
 * @license		http://philsturgeon.co.uk/code/dbad-license
 */

// --------------------------------------------------------------------------

/**
 * Base class for TestCases
 */
class Query_TestCase extends PHPUnit_Framework_TestCase {

	/**
	 * Wrapper for Simpletest's assertEqual
	 *
	 * @param mixed $expected
	 * @param mixed $actual
	 * @param string $message
	 */
	public function assertEqual($expected, $actual, $message='')
	{
		$this->assertEquals($expected, $actual, $message);
	}

	/**
	 * Wrapper for SimpleTest's assertIsA
	 *
	 * @param object $object
	 * @param string $type
	 * @param string $message
	 */
	public function assertIsA($object, $type, $message='')
	{
		$this->assertTrue(is_a($object, $type), $message);
	}

	/**
	 * Implementation of SimpleTest's assertReference
	 *
	 * @param mixed $first
	 * @param mixed $second
	 * @param string $message
	 */
	public function assertReference($first, $second, $message='')
	{
		if (is_object($first))
		{
            $res = ($first === $second);
        }
        else
        {
			$temp = $first;
	        $first = uniqid("test");
	        $is_ref = ($first === $second);
	        $first = $temp;
	        $res = $is_ref;
        }
        $this->assertTrue($res, $message);
	}
}

// --------------------------------------------------------------------------

/**
 * Unit test bootstrap - Using phpunit
 */
define('QTEST_DIR', realpath(dirname(__FILE__)));
define('QBASE_DIR', realpath(QTEST_DIR.'/../') . '/');
define('QDS', DIRECTORY_SEPARATOR);

// Include db classes
require_once(QBASE_DIR . 'autoload.php');

// Require base testing classes
require_once(QTEST_DIR . '/core/core.php');
require_once(QTEST_DIR . '/core/db_test.php');
require_once(QTEST_DIR . '/core/db_qp_test.php');
require_once(QTEST_DIR . '/core/db_qb_test.php');
require_once(QTEST_DIR . '/core/table_builder.php');

// Preset SQLite connection, so there aren't locking issues
if (extension_loaded('pdo_sqlite'))
{
	$path = QTEST_DIR.QDS.'db_files'.QDS.'test_sqlite.db';
	@unlink($path);
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
}

// If Firebird (interbase) extension does not exist,
// create a fake class to suppress errors from skipped tests
if ( ! function_exists('fbird_connect'))
{
	class Firebird {
		public $sql;
		public $util;
	}
}

// End of bootstrap.php