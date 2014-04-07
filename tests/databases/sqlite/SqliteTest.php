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
	}

	// --------------------------------------------------------------------------
	// ! Util Method tests
	// --------------------------------------------------------------------------

	public function testCreateTable()
	{
		//Attempt to create the table
		$sql = $this->db->util->create_table('create_test',
			array(
				'id' => 'INTEGER',
				'key' => 'TEXT',
				'val' => 'TEXT',
			),
			array(
				'id' => 'PRIMARY KEY'
			)
		);
		$this->db->query($sql);

		//Attempt to create the table
		$sql = $this->db->util->create_table('create_join',
			array(
				'id' => 'INTEGER',
				'key' => 'TEXT',
				'val' => 'TEXT',
			),
			array(
				'id' => 'PRIMARY KEY'
			)
		);
		$this->db->query($sql);

		// A table to delete
		$sql = $this->db->util->create_table('create_delete',
			array(
				'id' => 'INTEGER',
				'key' => 'TEXT',
				'val' => 'TEXT',
			),
			array(
				'id' => 'PRIMARY KEY'
			)
		);
		$this->db->query($sql);

		//Check
		$dbs = $this->db->get_tables();

		$this->assertTrue(in_array('create_test', $dbs));
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

	public function testGetTables()
	{
		$tables = $this->db->get_tables();
		$this->assertTrue(is_array($tables));
	}

	// --------------------------------------------------------------------------

	public function testGetSystemTables()
	{
		$tables = $this->db->get_system_tables();

		$this->assertTrue(is_array($tables));
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
		$res = $this->db->beginTransaction();

		$sql = 'INSERT INTO "create_test" ("id", "key", "val") VALUES (10, 12, 14)';
		$this->db->query($sql);

		$res = $this->db->commit();
		$this->assertTrue($res);
	}

	// --------------------------------------------------------------------------

	public function testRollbackTransaction()
	{
		$res = $this->db->beginTransaction();

		$sql = 'INSERT INTO "create_test" ("id", "key", "val") VALUES (182, 96, 43)';
		$this->db->query($sql);

		$res = $this->db->rollback();
		$this->assertTrue($res);
	}

	// --------------------------------------------------------------------------

	public function testGetDBs()
	{
		$this->assertNull($this->db->get_dbs());
	}

	// --------------------------------------------------------------------------

	public function testGetSchemas()
	{
		$this->assertNull($this->db->get_schemas());
	}

	// --------------------------------------------------------------------------

	public function testGetTypes()
	{
		$this->assertTrue(is_array($this->db->get_types()));
	}

	// --------------------------------------------------------------------------
	// ! SQL tests
	// --------------------------------------------------------------------------

	public function testNullMethods()
	{
		$sql = $this->db->sql->system_table_list();
		$this->assertEqual(NULL, $sql);

		$sql = $this->db->sql->trigger_list();
		$this->assertEqual(NULL, $sql);

		$sql = $this->db->sql->function_list();
		$this->assertEqual(NULL, $sql);

		$sql = $this->db->sql->procedure_list();
		$this->assertEqual(NULL, $sql);

		$sql = $this->db->sql->sequence_list();
		$this->assertEqual(NULL, $sql);

		$sql = $this->db->sql->fk_list('create_test');
		$this->assertEqual(NULL, $sql);
	}

	// --------------------------------------------------------------------------

	public function testGetFKs()
	{
		$keys = $this->db->get_fks('create_test');
		$this->assertNull($keys);
	}
}