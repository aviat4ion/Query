<?php

class Connection_Manager_Test extends Query_TestCase {

	protected static $instance = NULL;

	public static function setUpBeforeClass()
	{
		self::$instance = Query\ConnectionManager::get_instance();
	}

	// --------------------------------------------------------------------------

	public function testNoClone()
	{
		$this->expectException('DomainException');
		$this->expectExceptionMessage("Can't clone singleton");
		$clone = clone self::$instance;
	}

	// --------------------------------------------------------------------------

	public function testNoSerialize()
	{
		$this->setExpectedException('DomainException', "No serializing of singleton");
		$string = serialize(self::$instance);

		$this->setExpectedException('DomainException', "No serializing of singleton");
		$string = self::$instance->__sleep();
	}

	// --------------------------------------------------------------------------

	public function testNoUnserialize()
	{
		$this->setExpectedException('DomainException', "Can't unserialize singleton");
		$obj = self::$instance->__wakeup();
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

		$this->assertEqual($expected, self::$instance->parse_params($params));
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
		$this->assertInstanceOf('Query\\QueryBuilder', $conn);


		// Check that the connection just made is returned from the get_connection method
		$this->assertEqual($conn, self::$instance->get_connection());
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
		$this->assertInstanceOf('Query\\QueryBuilder', $conn);

		$this->assertEqual($conn, self::$instance->get_connection('conn_manager'));
	}
}
// End of connection_manager_test.php