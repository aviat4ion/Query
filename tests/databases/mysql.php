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
 * MySQLTest class.
 * 
 * @extends UnitTestCase
 */
class MySQLTest extends DBTest {
	
	function setUp()
	{
		// Attempt to connect, if there is a test config file
		if (is_file("../test_config.json"))
		{
			$params = json_decode(file_get_contents("../test_config.json"));
			$params = $params->mysql;
			
			$this->db = new MySQL("host={$params->host};port={$params->port};dbname={$params->database}", $params->user, $params->pass);
		}
		elseif ( ! empty($_ENV['TRAVIS']))
		{
			$this->db = new MySQL('host=127.0.0.1;dbname=test', 'root');
		}
	}
	
	function TestExists()
	{
		$this->assertTrue(in_array('mysql', pdo_drivers()));
	}
	
	function TestConnection()
	{
		if (empty($this->db))  return; 
	
		$this->assertIsA($this->db, 'MySQL');
	}

	function TestCreateTable()
	{
		if (empty($this->db))  return; 
	
		//Attempt to create the table
		$sql = $this->db->sql->create_table('create_test', 
			array(
				'id' => 'int(10)',
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
				'id' => 'int(10)',
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
}
 
