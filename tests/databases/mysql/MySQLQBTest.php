<?php
/**
 * Query
 *
 * Free Query Builder / Database Abstraction Layer
 *
 * @package		Query
 * @author		Timothy J. Warren
 * @copyright	Copyright (c) 2012 - 2014
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
		// If the database isn't installed, skip the tests
		if ( ! class_exists("\\Query\\Driver\\MySQL"))
		{
			$this->markTestSkipped("MySQL extension for PDO not loaded");
		}

 		// Attempt to connect, if there is a test config file
		if (is_file(QTEST_DIR . "/settings.json"))
		{
			$params = json_decode(file_get_contents(QTEST_DIR . "/settings.json"));
			$params = $params->mysql;
			$params->type = "MySQL";
			$params->options = array();
			$params->options[PDO::ATTR_PERSISTENT]  = TRUE;
		}
		elseif (($var = getenv('CI'))) // Travis CI Connection Info
		{
			$params = array(
				'host' => '127.0.0.1',
				'port' => '3306',
				'database' => 'test',
				'user' => 'root',
				'pass' => NULL,
				'type' => 'mysql'
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
			->get('test', 2, 1);

		$res = $query->fetchAll(PDO::FETCH_ASSOC);

		$expected = array (
		  array (
		    'id' => '1',
		    'select_type' => 'SIMPLE',
		    'table' => 'test',
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