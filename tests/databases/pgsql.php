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
 * PgTest class.
 * 
 * @extends UnitTestCase
 */
class PgTest extends DBTest {

	function __construct()
	{
		parent::__construct();
	}
	
	function setUp()
	{
		// Attempt to connect, if there is a test config file
		if (is_file("../test_config.json"))
		{
			$params = json_decode(file_get_contents("../test_config.json"));
			$params = $params->pgsql;
			
			$this->db = new PgSQL("host={$params->host};port={$params->port};dbname={$params->database}", $params->user, $params->pass);
		}
		elseif (($var = getenv('CI')))
		{
			$this->db = new PgSQL('host=127.0.0.1;port=5432;dbname=test', 'postgres');
		}
	}
	
	function TestExists()
	{
		$this->assertTrue(in_array('pgsql', pdo_drivers()));
	}
	
	function TestConnection()
	{
		if (empty($this->db))  return; 
	
		$this->assertIsA($this->db, 'PgSQL');
	}

	function TestCreateTable()
	{
		if (empty($this->db))  return; 
		
		// Drop the table(s) if they exist
		$sql = 'DROP TABLE IF EXISTS "create_test"';
		$this->db->query($sql);
		$sql = 'DROP TABLE IF EXISTS "create_join"';
		$this->db->query($sql);
		
	
		//Attempt to create the table
		$sql = $this->db->sql->create_table('create_test', 
			array(
				'id' => 'integer',
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
				'id' => 'integer',
				'key' => 'TEXT',
				'val' => 'TEXT',
			), 
			array(
				'id' => 'PRIMARY KEY'
			)
		);
		$this->db->query($sql);
		
		//echo $sql.'<br />';
		
		//Reset
		unset($this->db);
		$this->setUp();

		//Check
		$dbs = $this->db->get_tables();
		print_r($dbs);
		$this->assertTrue(in_array('create_test', $dbs));
	
	}
}