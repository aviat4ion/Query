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
 * SQLiteTest class.
 *
 * @extends DBTest
 * @requires extension pdo_sqlite
 */
class SQLiteTest extends DBTest {

	public static function setupBeforeClass()
	{
		$path = QTEST_DIR.QDS.'db_files'.QDS.'test_sqlite.db';
		$params = array(
			'type' => 'sqlite',
			'file' => ':memory:',
			'prefix' => 'create_',
			'alias' => 'test_sqlite',
			'options' => array(
				PDO::ATTR_PERSISTENT => TRUE
			)
		);

		self::$db = Query($params);
		self::$db->setTablePrefix('create_');
	}

	// --------------------------------------------------------------------------
	// ! Util Method tests
	// --------------------------------------------------------------------------

	public function testCreateTable()
	{
		self::$db->exec(file_get_contents(QTEST_DIR.'/db_files/sqlite.sql'));

		//Check
		$dbs = self::$db->getTables();

		$this->assertTrue(in_array('TEST1', $dbs));
		$this->assertTrue(in_array('TEST2', $dbs));
		$this->assertTrue(in_array('NUMBERS', $dbs));
		$this->assertTrue(in_array('NEWTABLE', $dbs));
		$this->assertTrue(in_array('create_test', $dbs));
		$this->assertTrue(in_array('create_join', $dbs));
		$this->assertTrue(in_array('create_delete', $dbs));
	}

	// --------------------------------------------------------------------------

	/*public function testBackupData()
	{
		$sql = mb_trim(self::$db->getUtil()->backupData(array('create_join', 'create_test')));

		$sqlArray = explode("\n", $sql);

		$expected = <<<SQL
INSERT INTO "create_test" ("id","key","val") VALUES (1,'boogers','Gross');
INSERT INTO "create_test" ("id","key","val") VALUES (2,'works','also?');
INSERT INTO "create_test" ("id","key","val") VALUES (10,12,14);
INSERT INTO "create_test" ("id","key","val") VALUES (587,1,2);
INSERT INTO "create_test" ("id","key","val") VALUES (999,'''ring''','''sale''');
SQL;
		$expectedArray = explode("\n", $expected);
		$this->assertEqual($expectedArray, $sqlArray);
	}*/

	// --------------------------------------------------------------------------

	public function testBackupStructure()
	{
		$sql = mb_trim(self::$db->getUtil()->backupStructure());
		$expected = <<<SQL
CREATE TABLE "create_test" ("id" INTEGER PRIMARY KEY, "key" TEXT, "val" TEXT);
CREATE TABLE "create_join" ("id" INTEGER PRIMARY KEY, "key" TEXT, "val" TEXT);
CREATE TABLE "create_delete" ("id" INTEGER PRIMARY KEY, "key" TEXT, "val" TEXT);
CREATE TABLE TEST1 (
  TEST_NAME TEXT NOT NULL,
  TEST_ID INTEGER DEFAULT '0' NOT NULL,
  TEST_DATE TEXT NOT NULL,
  CONSTRAINT PK_TEST PRIMARY KEY (TEST_ID)
);
CREATE TABLE TEST2 (
  ID INTEGER NOT NULL,
  FIELD1 INTEGER,
  FIELD2 TEXT,
  FIELD3 TEXT,
  FIELD4 INTEGER,
  FIELD5 INTEGER,
  ID2 INTEGER NOT NULL,
  CONSTRAINT PK_TEST2 PRIMARY KEY (ID2),
  CONSTRAINT TEST2_FIELD1ID_IDX UNIQUE (ID, FIELD1),
  CONSTRAINT TEST2_FIELD4_IDX UNIQUE (FIELD4)
);
;
;
CREATE INDEX TEST2_FIELD5_IDX ON TEST2 (FIELD5);
CREATE TABLE NUMBERS (
  NUMBER INTEGER DEFAULT 0 NOT NULL,
  EN TEXT NOT NULL,
  FR TEXT NOT NULL
);
CREATE TABLE NEWTABLE (
  ID INTEGER DEFAULT 0 NOT NULL,
  SOMENAME TEXT,
  SOMEDATE TEXT NOT NULL,
  CONSTRAINT PKINDEX_IDX PRIMARY KEY (ID)
);
CREATE VIEW "testview" AS
SELECT *
FROM TEST1
WHERE TEST_NAME LIKE 't%';
CREATE VIEW "numbersview" AS
SELECT *
FROM NUMBERS
WHERE NUMBER > 100;
CREATE TABLE "testconstraints" (
  someid integer NOT NULL,
  somename TEXT NOT NULL,
  CONSTRAINT testconstraints_id_pk PRIMARY KEY (someid)
);
CREATE TABLE "testconstraints2" (
  ext_id integer NOT NULL,
  modified text,
  uniquefield text NOT NULL,
  usraction integer NOT NULL,
  CONSTRAINT testconstraints_id_fk FOREIGN KEY (ext_id)
      REFERENCES testconstraints (someid)
      ON UPDATE CASCADE
	  ON DELETE CASCADE,
  CONSTRAINT unique_2_fields_idx UNIQUE (modified, usraction),
  CONSTRAINT uniquefld_idx UNIQUE (uniquefield)
);
;
;
SQL;

		$expectedArray = explode("\n", $expected);
		$resultArray = explode("\n", $sql);

		$this->assertEqual($expectedArray, $resultArray);
	}

	// --------------------------------------------------------------------------

	public function testDeleteTable()
	{
		$sql = self::$db->getUtil()->deleteTable('create_delete');

		self::$db->query($sql);

		//Check
		$dbs = self::$db->getTables();
		$this->assertFalse(in_array('create_delete', $dbs));
	}

	// --------------------------------------------------------------------------
	// ! General tests
	// --------------------------------------------------------------------------

	public function testConnection()
	{
		$class = '\\Query\\Drivers\\Sqlite\\Driver';

		$db = new $class(QTEST_DIR.QDS.'db_files'.QDS.'test_sqlite.db');

		$this->assertIsA($db, $class);
		$this->assertIsA(self::$db->db, $class);

		unset($db);
	}

	// --------------------------------------------------------------------------

	public function testTruncate()
	{
		self::$db->truncate('create_test');
	}

	// --------------------------------------------------------------------------

	public function testPreparedStatements()
	{
		$sql = <<<SQL
			INSERT INTO "create_test" ("id", "key", "val")
			VALUES (?,?,?)
SQL;
		$statement = self::$db->prepareQuery($sql, array(1,"boogers", "Gross"));

		$statement->execute();

	}

	// --------------------------------------------------------------------------

	public function testPrepareExecute()
	{
		$sql = <<<SQL
			INSERT INTO "create_test" ("id", "key", "val")
			VALUES (?,?,?)
SQL;
		self::$db->prepareExecute($sql, array(
			2, "works", 'also?'
		));

	}

	// --------------------------------------------------------------------------

	public function testCommitTransaction()
	{
		if (IS_QUERCUS)
		{
			$this->markTestSkipped("JDBC Driver doesn't support transactions");
		}

		$res = self::$db->beginTransaction();

		$sql = 'INSERT INTO "create_test" ("id", "key", "val") VALUES (10, 12, 14)';
		self::$db->query($sql);

		$res = self::$db->commit();
		$this->assertTrue($res);
	}

	// --------------------------------------------------------------------------

	public function testRollbackTransaction()
	{
		if (IS_QUERCUS)
		{
			$this->markTestSkipped("JDBC Driver doesn't support transactions");
		}

		$res = self::$db->beginTransaction();

		$sql = 'INSERT INTO "create_test" ("id", "key", "val") VALUES (182, 96, 43)';
		self::$db->query($sql);

		$res = self::$db->rollback();
		$this->assertTrue($res);
	}

	// --------------------------------------------------------------------------

	public function testGetDBs()
	{
		$this->assertTrue(is_array(self::$db->getDbs()));
	}

	// --------------------------------------------------------------------------

	public function testGetSchemas()
	{
		$this->assertNull(self::$db->getSchemas());
	}

	// --------------------------------------------------------------------------
	// ! SQL tests
	// --------------------------------------------------------------------------

	public function testNullMethods()
	{
		$sql = self::$db->sql->functionList();
		$this->assertEqual(NULL, $sql);

		$sql = self::$db->sql->procedureList();
		$this->assertEqual(NULL, $sql);

		$sql = self::$db->sql->sequenceList();
		$this->assertEqual(NULL, $sql);
	}

	// --------------------------------------------------------------------------

	public function testGetSystemTables()
	{
		$sql = self::$db->getSystemTables();
		$this->assertTrue(is_array($sql));
	}

	// --------------------------------------------------------------------------

	public function testGetSequences()
	{
		$this->assertNull(self::$db->getSequences());
	}

	// --------------------------------------------------------------------------

	public function testGetFunctions()
	{
		$this->assertNull(self::$db->getFunctions());
	}

	// --------------------------------------------------------------------------

	public function testGetProcedures()
	{
		$this->assertNull(self::$db->getProcedures());
	}
}