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

use DomainException;
use Query\{ConnectionManager, QueryBuilderInterface};

class ConnectionManagerTest extends TestCase {

	protected static $instance;

	public static function setUpBeforeClass(): void
	{
		ConnectionManager::getInstance();
		self::$instance = ConnectionManager::getInstance();
	}

	public static function tearDownAfterClass(): void
	{
		self::$instance = NULL;
	}

	public function testNoClone(): void
	{
		$this->expectException('DomainException');
		$this->expectExceptionMessage("Can't clone singleton");
		$clone = clone self::$instance;
		$this->assertNull($clone);
	}

	public function testNoSerialize(): void
	{
		$this->expectException(DomainException::class);
		$this->expectExceptionMessage('No serializing of singleton');
		serialize(self::$instance);

		$this->expectException(DomainException::class);
		$this->expectExceptionMessage('No serializing of singleton');
		self::$instance->__sleep();
	}

	public function testNoUnserialize(): void
	{
		$this->expectException(DomainException::class);
		$this->expectExceptionMessage("Can't unserialize singleton");
		self::$instance->__wakeup();
	}

	public function testParseParams(): void
	{
		$params = new class {
			public $type = 'sqlite';
			public $file = ':memory:';
			public $options = [
				'foo' => 'bar'
			];
		};

		$expected = [
			':memory:',
			'Sqlite',
			$params,
			['foo' => 'bar']
		];

		$this->assertEqual($expected, self::$instance->parseParams($params));
	}

	public function testConnect(): void
	{
		$params = new class {
			public $type = 'sqlite';
			public $file = ':memory:';
			public $prefix = 'create_';
			public $options = [
				'foo' => 'bar'
			];
		};

		$conn = self::$instance->connect($params);
		$this->assertInstanceOf(QueryBuilderInterface::class, $conn);


		// Check that the connection just made is returned from the get_connection method
		$this->assertEqual($conn, self::$instance->getConnection());
	}

	public function testGetConnection(): void
	{
		$params = (object) [
			'type' => 'sqlite',
			'file' => ':memory:',
			'prefix' => 'create_',
			'alias' => 'conn_manager',
			'options' => [
				'foo' => 'bar'
			]
		];

		$conn = self::$instance->connect($params);
		$this->assertInstanceOf(QueryBuilderInterface::class, $conn);

		$this->assertEqual($conn, self::$instance->getConnection('conn_manager'));
	}
}
// End of connection_manager_test.php
