<?php
/**
 * Query
 *
 * Free Query Builder / Database Abstraction Layer
 *
 * @package		Query
 * @author		Timothy J. Warren
 * @copyright	Copyright (c) 2012
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
		$dbpath = TEST_DIR.DS.'db_files'.DS.'FB_TEST_DB.FDB';
		
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

	/*public function TestCreateTable()
	{
		//Attempt to create the table
		$sql = $this->db->sql->create_table('create_join', array(
			'id' => 'SMALLINT', 
			'key' => 'VARCHAR(64)', 
			'val' => 'BLOB SUB_TYPE TEXT'
		));
		$this->db->query($sql);
		
		//This test fails for an unknown reason, when clearly the table exists
		//Reset
		$this->tearDown();
		$this->setUp();
		
		//Check
		$table_exists = (bool)in_array('create_test', $this->tables);
		
		echo "create_test exists :".(int)$table_exists.'<br />';
		
		$this->assertTrue($table_exists);
	}*/
	
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
	
	public function TestPrepareQuery()
	{
		$this->assertFalse($this->db->prepare_query('', array()));	
	}
	
	// --------------------------------------------------------------------------

	/*public function TestDeleteTable()
	{
		//Attempt to delete the table
		$sql = $this->db->sql->delete_table('create_test');
		$this->db->query($sql);
		
		//Reset
		$this->tearDown();
		$this->setUp();
		
		//Check
		$table_exists = in_array('create_test', $this->tables);
		$this->assertFalse($table_exists);
	}*/
	
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
	
	public function TestGetpublic functions()
	{
		$this->assertTrue(is_array($this->db->get_public functions()));
	}
	
	// --------------------------------------------------------------------------
	
	public function TestGetTriggers()
	{
		$this->assertTrue(is_array($this->db->get_triggers()));
	}
}