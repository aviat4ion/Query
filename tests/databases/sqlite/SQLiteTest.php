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
 * SQLiteTest class.
 *
 * @extends DBTest
 * @requires extension pdo_sqlite
 */
class SQLiteTest extends DBTest {

	public function setUp()
	{
		// Set up in the bootstrap to mitigate
		// connection locking issues
		$this->db = Query('test_sqlite');
		$this->db->table_prefix = 'create_';
	}

	// --------------------------------------------------------------------------
	// ! Util Method tests
	// --------------------------------------------------------------------------

	public function testCreateTable()
	{
		$this->db->exec(file_get_contents(QTEST_DIR.'/db_files/sqlite.sql'));

		//Check
		$dbs = $this->db->get_tables();

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
		$sql = mb_trim($this->db->util->backup_data(array('create_join', 'create_test')));

		$sql_array = explode("\n", $sql);

		$expected = <<<SQL
INSERT INTO "create_test" ("id","key","val") VALUES (1,'boogers','Gross');
INSERT INTO "create_test" ("id","key","val") VALUES (2,'works','also?');
INSERT INTO "create_test" ("id","key","val") VALUES (10,12,14);
INSERT INTO "create_test" ("id","key","val") VALUES (587,1,2);
INSERT INTO "create_test" ("id","key","val") VALUES (999,'''ring''','''sale''');
SQL;
		$expected_array = explode("\n", $expected);
		$this->assertEqual($expected_array, $sql_array);
	}*/

	// --------------------------------------------------------------------------

	public function testBackupStructure()
	{
		$sql = mb_trim($this->db->util->backup_structure());
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

		$expected_array = explode("\n", $expected);
		$result_array = explode("\n", $sql);

		$this->assertEqual($expected_array, $result_array);
	}

	// --------------------------------------------------------------------------

	public function testDeleteTable()
	{
		$sql = $this->db->util->delete_table('create_delete');

		$this->db->query($sql);

		//Check
		$dbs = $this->db->get_tables();
		$this->assertFalse(in_array('create_delete', $dbs));
	}

	// --------------------------------------------------------------------------
	// ! General tests
	// --------------------------------------------------------------------------

	public function testConnection()
	{
		$db = new \Query\Driver\SQLite(QTEST_DIR.QDS.'db_files'.QDS.'test_sqlite.db');

		$this->assertIsA($db, '\\Query\\Driver\\SQLite');
		$this->assertIsA($this->db->db, '\\Query\\Driver\\SQLite');

		unset($db);
	}

	// --------------------------------------------------------------------------

	public function testTruncate()
	{
		$this->db->truncate('create_test');
	}

	// --------------------------------------------------------------------------

	public function testPreparedStatements()
	{
		$sql = <<<SQL
			INSERT INTO "create_test" ("id", "key", "val")
			VALUES (?,?,?)
SQL;
		$statement = $this->db->prepare_query($sql, array(1,"boogers", "Gross"));

		$statement->execute();

	}

	// --------------------------------------------------------------------------

	public function testPrepareExecute()
	{
		$sql = <<<SQL
			INSERT INTO "create_test" ("id", "key", "val")
			VALUES (?,?,?)
SQL;
		$this->db->prepare_execute($sql, array(
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

		$res = $this->db->beginTransaction();

		$sql = 'INSERT INTO "create_test" ("id", "key", "val") VALUES (10, 12, 14)';
		$this->db->query($sql);

		$res = $this->db->commit();
		$this->assertTrue($res);
	}

	// --------------------------------------------------------------------------

	public function testRollbackTransaction()
	{
		if (IS_QUERCUS)
		{
			$this->markTestSkipped("JDBC Driver doesn't support transactions");
		}

		$res = $this->db->beginTransaction();

		$sql = 'INSERT INTO "create_test" ("id", "key", "val") VALUES (182, 96, 43)';
		$this->db->query($sql);

		$res = $this->db->rollback();
		$this->assertTrue($res);
	}

	// --------------------------------------------------------------------------

	public function testGetDBs()
	{
		$this->assertTrue(is_array($this->db->get_dbs()));
	}

	// --------------------------------------------------------------------------

	public function testGetSchemas()
	{
		$this->assertNull($this->db->get_schemas());
	}

	// --------------------------------------------------------------------------
	// ! SQL tests
	// --------------------------------------------------------------------------

	public function testNullMethods()
	{
		$sql = $this->db->sql->function_list();
		$this->assertEqual(NULL, $sql);

		$sql = $this->db->sql->procedure_list();
		$this->assertEqual(NULL, $sql);

		$sql = $this->db->sql->sequence_list();
		$this->assertEqual(NULL, $sql);
	}

	// --------------------------------------------------------------------------

	public function testGetSystemTables()
	{
		$sql = $this->db->get_system_tables();
		$this->assertTrue(is_array($sql));
	}

	// --------------------------------------------------------------------------

	public function testGetSequences()
	{
		$this->assertNull($this->db->get_sequences());
	}

	// --------------------------------------------------------------------------

	public function testGetFunctions()
	{
		$this->assertNull($this->db->get_functions());
	}

	// --------------------------------------------------------------------------

	public function testGetProcedures()
	{
		$this->assertNull($this->db->get_procedures());
	}
}