<?php
/**
 * OpenSQLManager
 *
 * Free Database manager for Open Source Databases
 *
 * @author 		Timothy J. Warren
 * @copyright	Copyright (c) 2012
 * @link 		https://github.com/aviat4ion/OpenSQLManager
 * @license 	http://philsturgeon.co.uk/code/dbad-license
 */

// --------------------------------------------------------------------------

/**
 * SQLiteTest class.
 *
 * @extends UnitTestCase
 */
class SQLiteTest extends UnitTestCase {

	public function __construct()
	{
		parent::__construct();
	}
	
	// --------------------------------------------------------------------------

	public function setUp()
	{
		$path = QTEST_DIR.QDS.'db_files'.QDS.'test_sqlite.db';
		$this->db = new SQLite($path);
	}
	
	// --------------------------------------------------------------------------

	public function tearDown()
	{
		unset($this->db);
	}
	
	// --------------------------------------------------------------------------

	public function TestConnection()
	{
		$this->assertIsA($this->db, 'SQLite');
	}
	
	// --------------------------------------------------------------------------

	public function TestGetTables()
	{
		$tables = $this->db->get_tables();
		$this->assertTrue(is_array($tables));
	}
	
	// --------------------------------------------------------------------------

	public function TestGetSystemTables()
	{
		$tables = $this->db->get_system_tables();

		$this->assertTrue(is_array($tables));
	}
	
	// --------------------------------------------------------------------------

	public function TestCreateTransaction()
	{
		$res = $this->db->beginTransaction();
		$this->assertTrue($res);
	}
	
	// --------------------------------------------------------------------------

	public function TestCreateTable()
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

		//Check
		$dbs = $this->db->get_tables();

		$this->assertTrue(in_array('create_test', $dbs));
	}
	
	// --------------------------------------------------------------------------

	public function TestTruncate()
	{
		$this->db->truncate('create_test');
		$this->assertIsA($this->db->affected_rows(), 'int');
	}
	
	// --------------------------------------------------------------------------

	public function TestPreparedStatements()
	{
		$sql = <<<SQL
			INSERT INTO "create_test" ("id", "key", "val")
			VALUES (?,?,?)
SQL;
		$statement = $this->db->prepare_query($sql, array(1,"boogers", "Gross"));

		$statement->execute();

	}
	
	// --------------------------------------------------------------------------

	public function TestPrepareExecute()
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

	public function TestCommitTransaction()
	{
		$res = $this->db->beginTransaction();

		$sql = 'INSERT INTO "create_test" ("id", "key", "val") VALUES (10, 12, 14)';
		$this->db->query($sql);

		$res = $this->db->commit();
		$this->assertTrue($res);
	}
	
	// --------------------------------------------------------------------------

	public function TestRollbackTransaction()
	{
		$res = $this->db->beginTransaction();

		$sql = 'INSERT INTO "create_test" ("id", "key", "val") VALUES (182, 96, 43)';
		$this->db->query($sql);

		$res = $this->db->rollback();
		$this->assertTrue($res);
	}
	
	// --------------------------------------------------------------------------

	// This is really time intensive ! Run only when needed
	/*public function TestDeleteTable()
	{
		//Make sure the table exists to delete
		$dbs = $this->db->get_tables();
		$this->assertTrue(isset($dbs['create_test']));

		//Attempt to delete the table
		$sql = $this->db->sql->delete_table('create_test');
		$this->db->query($sql);

		//Check
		$dbs = $this->db->get_tables();
		$this->assertFalse(in_array('create_test', $dbs));
	}*/
	
	// --------------------------------------------------------------------------

	public function TestGetDBs()
	{
		$this->assertFalse($this->db->get_dbs());
	}
	
	// --------------------------------------------------------------------------

	public function TestGetSchemas()
	{
		$this->assertFalse($this->db->get_schemas());
	}
}