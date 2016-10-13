<?php declare(strict_types=1);
/**
 * Query
 *
 * SQL Query Builder / Database Abstraction Layer
 *
 * PHP version 7
 *
 * @package     Query
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2012 - 2016 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link        https://git.timshomepage.net/aviat4ion/Query
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

		$params = get_json_config();
		if (($var = getenv('TRAVIS')))
		{
			self::$db = new $class('host=127.0.0.1;port=5432;dbname=test', 'postgres');
		}
		// Attempt to connect, if there is a test config file
		else if ($params !== FALSE)
		{
			$params = $params->pgsql;
			self::$db = new $class("pgsql:host={$params->host};dbname={$params->database};port=5432", $params->user, $params->pass);
		}

		self::$db->set_table_prefix('create_');
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
		$sql = self::$db->get_util()->create_table('create_test',
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
		$sql = self::$db->get_util()->create_table('create_join',
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