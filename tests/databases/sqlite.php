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
 * SQLiteTest class.
 * 
 * @extends UnitTestCase
 */
class SQLiteTest extends DBTest {
	
	function setUp()
	{
		$path = TEST_DIR.DS.'test_dbs'.DS.'test_sqlite.db';
		$this->db = new SQLite($path);
	}
	
	function tearDown()
	{
		unset($this->db);
	}

	function TestConnection()
	{
		$this->assertIsA($this->db, 'SQLite');
	}
	
	
	function TestCreateTable()
	{
		//Attempt to create the table
		$sql = $this->db->sql->create_table('create_test', 
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
		$sql = $this->db->sql->create_table('create_join', 
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
		$this->assertEqual($dbs['create_test'], 'CREATE TABLE "create_test" (id INTEGER PRIMARY KEY, key TEXT , val TEXT )');
	}
	
	/*function TestTruncate()
	{
		$this->db->truncate('create_test');
		$this->assertIsA($this->db->affected_rows(), 'int');
	}*/
	
			
	// This is really time intensive ! Run only when needed
	/*function TestDeleteTable()
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

}