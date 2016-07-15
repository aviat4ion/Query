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
 * MySQLTest class.
 *
 * @extends DBTest
 * @requires extension pdo_mysql
 */
class MySQLTest extends DBTest {

	public static function setUpBeforeClass()
	{
		$params = get_json_config();
		if (($var = getenv('TRAVIS')))
		{
			self::$db = new \Query\Drivers\Mysql\Driver('host=127.0.0.1;port=3306;dbname=test', 'root');
		}
		// Attempt to connect, if there is a test config file
		else if ($params !== FALSE)
		{
			$params = $params->mysql;

			self::$db = new \Query\Drivers\Mysql\Driver("mysql:host={$params->host};dbname={$params->database}", $params->user, $params->pass, array(
				PDO::ATTR_PERSISTENT => TRUE
			));
		}

		self::$db->set_table_prefix('create_');
	}

	// --------------------------------------------------------------------------

	public function testExists()
	{
		$this->assertTrue(in_array('mysql', PDO::getAvailableDrivers()));
	}

	// --------------------------------------------------------------------------

	public function testConnection()
	{
		$this->assertIsA(self::$db, '\\Query\\Drivers\\Mysql\\Driver');
	}

	// --------------------------------------------------------------------------

	public function testCreateTable()
	{
		self::$db->exec(file_get_contents(QTEST_DIR.'/db_files/mysql.sql'));

		//Attempt to create the table
		$sql = self::$db->get_util()->create_table('test',
			array(
				'id' => 'int(10)',
				'key' => 'TEXT',
				'val' => 'TEXT',
			),
			array(
				'id' => 'PRIMARY KEY'
			)
		);

		self::$db->query($sql);

		//Attempt to create the table
		$sql = self::$db->get_util()->create_table('join',
			array(
				'id' => 'int(10)',
				'key' => 'TEXT',
				'val' => 'TEXT',
			),
			array(
				'id' => 'PRIMARY KEY'
			)
		);
		self::$db->query($sql);

		//Check
		$dbs = self::$db->get_tables();

		$this->assertTrue(in_array('create_test', $dbs));

	}

	// --------------------------------------------------------------------------

	public function testTruncate()
	{
		self::$db->truncate('test');
		self::$db->truncate('join');
	}

	// --------------------------------------------------------------------------

	public function testPreparedStatements()
	{
		$sql = <<<SQL
			INSERT INTO `create_test` (`id`, `key`, `val`)
			VALUES (?,?,?)
SQL;
		$statement = self::$db->prepare_query($sql, array(1,"boogers", "Gross"));

		$res = $statement->execute();

		$this->assertTrue($res);

	}

	// --------------------------------------------------------------------------

	public function testBadPreparedStatement()
	{
		$sql = <<<SQL
			INSERT INTO `create_test` (`id`, `key`, `val`)
			VALUES (?,?,?)
SQL;
		try
		{
			$statement = self::$db->prepare_query($sql, 'foo');
		}
		catch(InvalidArgumentException $e)
		{
			$this->assertTrue(TRUE);
		}

	}

	// --------------------------------------------------------------------------

	public function testPrepareExecute()
	{
		$sql = <<<SQL
			INSERT INTO `create_test` (`id`, `key`, `val`)
			VALUES (?,?,?)
SQL;
		$res = self::$db->prepare_execute($sql, array(
			2, "works", 'also?'
		));

		$this->assertInstanceOf('PDOStatement', $res);

	}

	// --------------------------------------------------------------------------

	public function testCommitTransaction()
	{
		$res = self::$db->beginTransaction();

		$sql = 'INSERT INTO `create_test` (`id`, `key`, `val`) VALUES (10, 12, 14)';
		self::$db->query($sql);

		$res = self::$db->commit();
		$this->assertTrue($res);
	}

	// --------------------------------------------------------------------------

	public function testRollbackTransaction()
	{
		$res = self::$db->beginTransaction();

		$sql = 'INSERT INTO `create_test` (`id`, `key`, `val`) VALUES (182, 96, 43)';
		self::$db->query($sql);

		$res = self::$db->rollback();
		$this->assertTrue($res);
	}

	// --------------------------------------------------------------------------

	public function testGetSchemas()
	{
		$this->assertNull(self::$db->get_schemas());
	}

	// --------------------------------------------------------------------------

	public function testGetSequences()
	{
		$this->assertNull(self::$db->get_sequences());
	}

	// --------------------------------------------------------------------------

	public function testBackup()
	{
		$this->assertTrue(is_string(self::$db->get_util()->backup_structure()));
	}


}