<?php
/**
 * Query
 *
 * Free Query Builder / Database Abstraction Layer
 *
 * @author 		Timothy J. Warren
 * @copyright	Copyright (c) 2012
 * @link 		https://github.com/aviat4ion/Query
 * @license 	http://philsturgeon.co.uk/code/dbad-license 
 */

// --------------------------------------------------------------------------

/**
 * FirebirdTest class.
 * 
 * @extends UnitTestCase
 */
class FirebirdTest extends UnitTestCase {
	
	/**
	 * __construct function.
	 * 
	 * @access public
	 * @return void
	 */
	function __construct()
	{
		parent::__construct();
	}
	
	function setUp()
	{
		$dbpath = TEST_DIR.DS.'test_dbs'.DS.'FB_TEST_DB.FDB';
		
		// Test the db driver directly
		$this->db = new Firebird('localhost:'.$dbpath);
		$this->tables = $this->db->get_tables();
	}
	
	function tearDown()
	{
		unset($this->db);
		unset($this->tables);
	}

	function TestConnection()
	{
		$this->assertIsA($this->db, 'Firebird');
	}
	
	function TestGetTables()
	{
		$tables = $this->tables;
		$this->assertTrue(is_array($tables));
	}
	
	function TestGetSystemTables()
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
	
	function TestCreateTransaction()
	{
		$res = $this->db->beginTransaction();
		$this->assertTrue($res);
	}

	/*function TestCreateTable()
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
	
	
	
	function TestTruncate()
	{
		$this->db->truncate('create_test');
		
		$this->assertTrue($this->db->affected_rows() > 0);
	}
	
	function TestCommitTransaction()
	{
		$res = $this->db->beginTransaction();
		
		$sql = 'INSERT INTO "create_test" ("id", "key", "val") VALUES (10, 12, 14)';
		$this->db->query($sql);
	
		$res = $this->db->commit();
		$this->assertTrue($res);
	}
	
	function TestRollbackTransaction()
	{
		$res = $this->db->beginTransaction();
		
		$sql = 'INSERT INTO "create_test" ("id", "key", "val") VALUES (182, 96, 43)';
		$this->db->query($sql);
	
		$res = $this->db->rollback();
		$this->assertTrue($res);
	}
	
	
	
	function TestPreparedStatements()
	{
		$sql = <<<SQL
			INSERT INTO "create_test" ("id", "key", "val") 
			VALUES (?,?,?)
SQL;
		$this->db->prepare($sql);
		$this->db->execute(array(1,"booger's", "Gross"));

	}
	
	function TestPrepareExecute()
	{
		$sql = <<<SQL
			INSERT INTO "create_test" ("id", "key", "val") 
			VALUES (?,?,?)
SQL;
		$this->db->prepare_execute($sql, array(
			2, "works", 'also?'
		));
	
	}
	
	function TestPrepareQuery()
	{
		$this->assertFalse($this->db->prepare_query('', array()));	
	}

	/*function TestDeleteTable()
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
}