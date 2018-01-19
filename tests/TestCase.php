<?php declare(strict_types=1);
/**
 * Query
 *
 * SQL Query Builder / Database Abstraction Layer
 *
 * PHP version 7.1
 *
 * @package     Query
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2012 - 2018 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link        https://git.timshomepage.net/aviat4ion/Query
 */
namespace Query\Tests;

use PHPUnit\Framework\TestCase as PHPUnit_TestCase;

/**
 * Base class for TestCases
 */
class TestCase extends PHPUnit_TestCase {

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
	 * @param mixed $object
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
		if (\is_object($first))
		{
			$res = ($first === $second);
		}
		else
		{
			$temp = $first;
			$first = uniqid('test', TRUE);
			$isRef = ($first === $second);
			$first = $temp;
			$res = $isRef;
		}
		$this->assertTrue($res, $message);
	}
}