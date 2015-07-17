<?php
/**
 * OpenSQLManager
 *
 * Free Database manager for Open Source Databases
 *
 * @author 		Timothy J. Warren
 * @copyright	Copyright (c) 2012 - 2014
 * @link 		https://github.com/aviat4ion/OpenSQLManager
 * @license 	http://philsturgeon.co.uk/code/dbad-license
 */

// --------------------------------------------------------------------------

/**
 * PgTest class.
 *
 * @extends DBTest
 * @requires extension pdo_pgsql
 */
class PgTest extends DBTest {

	public function setUp()
	{
		$class = "Query\\Drivers\\Pgsql\\Driver";

		// If the database isn't installed, skip the tests
		if (( ! class_exists($class)) && ! IS_QUERCUS)
		{
			$this->markTestSkipped("Postgres extension for PDO not loaded");
		}

	}

	public static function setUpBeforeClass()
	{
		$class = "Query\\Drivers\\Pgsql\\Driver";

		// Attempt to connect, if there is a test config file
		if (is_file(QTEST_DIR . "/settings.json"))
		{
			$params = json_decode(file_get_contents(QTEST_DIR . "/settings.json"));
			$params = $params->pgsql;

			self::$db = new $class("pgsql:dbname={$params->database};port=5432", $params->user, $params->pass);
		}
		elseif (($var = getenv('CI')))
		{
			self::$db = new $class('host=127.0.0.1;port=5432;dbname=test', 'postgres');
		}

		self::$db->table_prefix = 'create_';
	}

	// --------------------------------------------------------------------------

	public function testExists()
	{
		$drivers = \PDO::getAvailableDrivers();
		$this->assertTrue(in_array('pgsql', $drivers));
	}

	// --------------------------------------------------------------------------

	public function testConnection()
	{
		if (empty(self::$db))  return;

		$this->assertIsA(self::$db, '\\Query\\Drivers\\Pgsql\\Driver');
	}

	// --------------------------------------------------------------------------

	public function testCreateTable()
	{
		self::$db->exec(file_get_contents(QTEST_DIR.'/db_files/pgsql.sql'));

		// Drop the table(s) if they exist
		$sql = 'DROP TABLE IF EXISTS "create_test"';
		self::$db->query($sql);
		$sql = 'DROP TABLE IF EXISTS "create_join"';
		self::$db->query($sql);


		//Attempt to create the table
		$sql = self::$db->util->create_table('create_test',
			array(
				'id' => 'integer',
				'key' => 'TEXT',
				'val' => 'TEXT',
			),
			array(
				'id' => 'PRIMARY KEY'
			)
		);

		self::$db->query($sql);

		//Attempt to create the table
		$sql = self::$db->util->create_table('create_join',
			array(
				'id' => 'integer',
				'key' => 'TEXT',
				'val' => 'TEXT',
			),
			array(
				'id' => 'PRIMARY KEY'
			)
		);
		self::$db->query($sql);

		//echo $sql.'<br />';

		//Reset
		//unset(self::$db);
		//$this->setUp();

		//Check
		$dbs = self::$db->get_tables();
		$this->assertTrue(in_array('create_test', $dbs));

	}

	// --------------------------------------------------------------------------

	public function testTruncate()
	{
		self::$db->truncate('create_test');
		self::$db->truncate('create_join');

		$ct_query = self::$db->query('SELECT * FROM create_test');
		$cj_query = self::$db->query('SELECT * FROM create_join');
	}

	// --------------------------------------------------------------------------

	public function testPreparedStatements()
	{
		$sql = <<<SQL
			INSERT INTO "create_test" ("id", "key", "val")
			VALUES (?,?,?)
SQL;
		$statement = self::$db->prepare_query($sql, array(1,"boogers", "Gross"));

		$statement->execute();

	}

	// --------------------------------------------------------------------------

	public function testBadPreparedStatement()
	{
		$sql = <<<SQL
			INSERT INTO "create_test" ("id", "key", "val")
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
		if (empty(self::$db))  return;

		$sql = <<<SQL
			INSERT INTO "create_test" ("id", "key", "val")
			VALUES (?,?,?)
SQL;
		self::$db->prepare_execute($sql, array(
			2, "works", 'also?'
		));

	}

	// --------------------------------------------------------------------------

	public function testCommitTransaction()
	{
		if (empty(self::$db))  return;

		$res = self::$db->beginTransaction();

		$sql = 'INSERT INTO "create_test" ("id", "key", "val") VALUES (10, 12, 14)';
		self::$db->query($sql);

		$res = self::$db->commit();
		$this->assertTrue($res);
	}

	// --------------------------------------------------------------------------

	public function testRollbackTransaction()
	{
		if (empty(self::$db))  return;

		$res = self::$db->beginTransaction();

		$sql = 'INSERT INTO "create_test" ("id", "key", "val") VALUES (182, 96, 43)';
		self::$db->query($sql);

		$res = self::$db->rollback();
		$this->assertTrue($res);
	}

	// --------------------------------------------------------------------------

	public function testGetSchemas()
	{
		$this->assertTrue(is_array(self::$db->get_schemas()));
	}

	// --------------------------------------------------------------------------

	public function testGetDBs()
	{
		$this->assertTrue(is_array(self::$db->get_dbs()));
	}

	// --------------------------------------------------------------------------

	public function testGetFunctions()
	{
		$this->assertNull(self::$db->get_functions());
	}
}