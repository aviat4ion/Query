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
 * @requires extension pdo_mysql
 */
class MySQLQBTest extends QBTest {

	public function setUp()
 	{
 		// Attempt to connect, if there is a test config file
		if (is_file(QBASE_DIR . "test_config.json"))
		{
			$params = json_decode(file_get_contents(QBASE_DIR . "test_config.json"));
			$params = $params->mysql;
			$params->type = "MySQL";
			$params->prefix = "create_";;
		}
		elseif (($var = getenv('CI'))) // Travis CI Connection Info
		{
			$params = array(
				'host' => '127.0.0.1',
				'port' => '3306',
				'database' => 'test',
				'user' => 'root',
				'pass' => NULL,
				'type' => 'mysql',
				'prefix' => 'create_'
			);
		}

		$this->db = Query($params);
 	}

	// --------------------------------------------------------------------------

	public function testExists()
	{
		$this->assertTrue(in_array('mysql', PDO::getAvailableDrivers()));
	}
	
	// --------------------------------------------------------------------------
	
	public function testQueryExplain()
	{
		$query = $this->db->select('id, key as k, val')
			->explain()
			->where('id >', 1)
			->where('id <', 900)
			->get('create_test', 2, 1);
			
		$res = $query->fetchAll(PDO::FETCH_ASSOC);
		
		$expected = array (
		  array (
		    'id' => '1',
		    'select_type' => 'SIMPLE',
		    'table' => 'create_test',
		    'type' => 'range',
		    'possible_keys' => 'PRIMARY',
		    'key' => 'PRIMARY',
		    'key_len' => '4',
		    'ref' => NULL,
		    'rows' => '1',
		    'filtered' => '100.00',
		    'Extra' => 'Using where',
		  )
		);
		
		$this->assertEqual($expected, $res);
	}
}