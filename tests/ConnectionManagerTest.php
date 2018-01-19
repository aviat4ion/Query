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

use DomainException;
use Query\{ConnectionManager, QueryBuilderInterface};

class ConnectionManagerTest extends TestCase {

	protected static $instance;

	public static function setUpBeforeClass()
	{
		self::$instance = ConnectionManager::getInstance();
	}

	// --------------------------------------------------------------------------

	public function testNoClone()
	{
		$this->expectException('DomainException');
		$this->expectExceptionMessage("Can't clone singleton");
		$clone = clone self::$instance;
		$this->assertNull($clone);
	}

	// --------------------------------------------------------------------------

	public function testNoSerialize()
	{
		$this->expectException(DomainException::class);
		$this->expectExceptionMessage('No serializing of singleton');
		serialize(self::$instance);

		$this->expectException(DomainException::class);
		$this->expectExceptionMessage('No serializing of singleton');
		self::$instance->__sleep();
	}

	// --------------------------------------------------------------------------

	public function testNoUnserialize()
	{
		$this->expectException(DomainException::class);
		$this->expectExceptionMessage("Can't unserialize singleton");
		self::$instance->__wakeup();
	}

	// --------------------------------------------------------------------------

	public function testParseParams()
	{
		$params = (object) array(
			'type' => 'sqlite',
			'file' => ':memory:',
			'options' => array(
				'foo' => 'bar'
			)
		);

		$expected = array(
			':memory:',
			'Sqlite',
			$params,
			array('foo' => 'bar')
		);

		$this->assertEqual($expected, self::$instance->parseParams($params));
	}

	// --------------------------------------------------------------------------

	public function testConnect()
	{
		$params = (object) array(
			'type' => 'sqlite',
			'file' => ':memory:',
			'prefix' => 'create_',
			'options' => array(
				'foo' => 'bar'
			)
		);

		$conn = self::$instance->connect($params);
		$this->assertInstanceOf(QueryBuilderInterface::class, $conn);


		// Check that the connection just made is returned from the get_connection method
		$this->assertEqual($conn, self::$instance->getConnection());
	}

	// --------------------------------------------------------------------------

	public function testGetConnection()
	{
		$params = (object) array(
			'type' => 'sqlite',
			'file' => ':memory:',
			'prefix' => 'create_',
			'alias' => 'conn_manager',
			'options' => array(
				'foo' => 'bar'
			)
		);

		$conn = self::$instance->connect($params);
		$this->assertInstanceOf(QueryBuilderInterface::class, $conn);

		$this->assertEqual($conn, self::$instance->getConnection('conn_manager'));
	}
}
// End of connection_manager_test.php
