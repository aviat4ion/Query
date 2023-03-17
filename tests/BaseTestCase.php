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
 * @version     4.1.0
 */

namespace Query\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Base class for TestCases
 */
abstract class BaseTestCase extends TestCase
{
	/**
	 * Wrapper for Simpletest's assertEqual
	 *
	 * @param mixed $expected
	 * @param mixed $actual
	 */
//	public function assertEqual($expected, $actual, $message='')
//	{
//		$this->assertEquals($expected, $actual, $message);
//	}

	/**
	 * Wrapper for SimpleTest's assertIsA
	 */
	public function assertIsA(mixed $object, string $type, string $message='')
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
//	public function assertReference($first, $second, $message='')
//	{
//		if (\is_object($first))
//		{
//			$res = ($first === $second);
//		}
//		else
//		{
//			$temp = $first;
//			$first = uniqid('test', TRUE);
//			$isRef = ($first === $second);
//			$first = $temp;
//			$res = $isRef;
//		}
//		$this->assertTrue($res, $message);
//	}
}
