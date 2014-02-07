<?php
/**
 * Query
 *
 * Free Query Builder / Database Abstraction Layer
 *
 * @package		Query
 * @author		Timothy J. Warren
 * @copyright	Copyright (c) 2012 - 2013
 * @link 		https://github.com/aviat4ion/Query
 * @license		http://philsturgeon.co.uk/code/dbad-license
 */

// --------------------------------------------------------------------------

/**
 * FirebirdTest class.
 * 
 * @extends UnitTestCase
 */
class FirebirdTest extends DBTest {
	
	public function setUp()
	{
		$dbpath = QTEST_DIR.QDS.'db_files'.QDS.'FB_TEST_DB.FDB';
		
		// Test the db driver directly
		$this->db = new Firebird('localhost:'.$dbpath);
		$this->tables = $this->db->get_tables();
	}
	
	// --------------------------------------------------------------------------
	
	public function tearDown()
	{
		unset($this->db);
		unset($this->tables);
	}
	
	// --------------------------------------------------------------------------
	
	/**
	 * coverage for methods in result class that aren't implemented
	 */
	public function TestNullResultMethods()
	{
		$obj = $this->db->query('SELECT "id" FROM "create_test"');
		
		$val = "bar";
			
		$this->assertNull($obj->bindColumn('foo', $val));
		$this->assertNull($obj->bindParam('foo', $val));
		$this->assertNull($obj->bindValue('foo', $val));
	
	}
	
	// --------------------------------------------------------------------------
	
	public function TestExists()
	{
		$this->assertTrue(function_exists('ibase_connect'));
		$this->assertTrue(function_exists('fbird_connect'));
	}	
	
	// --------------------------------------------------------------------------

	public function TestConnection()
	{
		$this->assertIsA($this->db, 'Firebird');
	}
	
	// --------------------------------------------------------------------------
	
	public function TestGetTables()
	{
		$tables = $this->tables;
		$this->assertTrue(is_array($tables));
	}
	
	// --------------------------------------------------------------------------
	
	public function TestGetSystemTables()
	{	
		$only_system = TRUE;
		
		$tables = $this->db->get_system_tables();
		
		foreach($tables as $t)
		{
			if(stripos($t, 'rdb$') !== 0 && stripos($t, 'mon$') !== 0)
			{
				$only_system = FALSE;
				break;
			}
		}
		
		$this->assertTrue($only_system);
	}
	
	// --------------------------------------------------------------------------
	
	public function TestCreateTransaction()
	{
		$res = $this->db->beginTransaction();
		$this->assertTrue($res);
	}
	
	// --------------------------------------------------------------------------
	// ! Create / Delete Tables
	// --------------------------------------------------------------------------

	public function TestCreateTable()
	{
		//Attempt to create the table
		$sql = $this->db->util->create_table('create_delete', array(
			'id' => 'SMALLINT', 
			'key' => 'VARCHAR(64)', 
			'val' => 'BLOB SUB_TYPE TEXT'
		));
		$this->db->query($sql);
		
		//Check
		$this->assertTrue(in_array('create_delete', $this->db->get_tables()));
	}
	
	public function TestDeleteTable()
	{
		//Attempt to delete the table
		$sql = $this->db->util->delete_table('create_delete');
		$this->db->query($sql);
		
		//Check
		$table_exists = in_array('create_delete', $this->db->get_tables());
		$this->assertFalse($table_exists);
	}
	
	// --------------------------------------------------------------------------
	
	public function TestTruncate()
	{
		$this->db->truncate('create_test');
		
		$this->assertTrue($this->db->affected_rows() > 0);
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
	
	public function TestPreparedStatements()
	{
		$sql = <<<SQL
			INSERT INTO "create_test" ("id", "key", "val") 
			VALUES (?,?,?)
SQL;
		$query = $this->db->prepare($sql);
		$query->execute(array(1,"booger's", "Gross"));

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
	
	public function TestFetch()
	{
		$res = $this->db->query('SELECT "key","val" FROM "create_test"');
		
		// Object
		$fetchObj = $res->fetchObject();
		$this->assertIsA($fetchObj, 'stdClass');
		
		// Associative array
		$fetchAssoc = $res->fetch(PDO::FETCH_ASSOC);
		$this->assertTrue(array_key_exists('key', $fetchAssoc));
		
		// Numeric array
		$res2 = $this->db->query('SELECT "id","key","val" FROM "create_test"');
		$fetch = $res2->fetch(PDO::FETCH_NUM);
		$this->assertTrue(is_array($fetch));
	}
	
	// --------------------------------------------------------------------------
	
	public function TestPrepareQuery()
	{
		$this->assertFalse($this->db->prepare_query('', array()));	
	}
	
	// --------------------------------------------------------------------------
	
	public function TestGetSequences()
	{
		$this->assertTrue(is_array($this->db->get_sequences()));
	}
	
	// --------------------------------------------------------------------------
	
	public function TestGetProcedures()
	{
		$this->assertTrue(is_array($this->db->get_procedures()));
	}
	
	// --------------------------------------------------------------------------
	
	public function TestGetFunctions()
	{
		$this->assertTrue(is_array($this->db->get_functions()));
	}
	
	// --------------------------------------------------------------------------
	
	public function TestGetTriggers()
	{
		$this->assertTrue(is_array($this->db->get_triggers()));
	}
}