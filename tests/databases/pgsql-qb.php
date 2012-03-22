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

class PgSQLQBTest extends QBTest {

	function __construct()
 	{
 		parent::__construct();
 		
 		// Attempt to connect, if there is a test config file
		if (is_file("../test_config.json"))
		{
			$params = json_decode(file_get_contents("../test_config.json"));
			$params = $params->pgsql;
			$params->type = "pgsql";
			
			$this->db = new Query_Builder($params);
			
			// echo '<hr /> Postgres Queries <hr />';		
			
		}
		elseif (($var = getenv('CI')))
		{
			$params = array(
				'host' => '127.0.0.1',
				'port' => '5432',
				'database' => 'test',
				'user' => 'postgres',
				'pass' => '',
				'type' => 'pgsql'
			);
		
			$this->db = new Query_Builder($params);
		}
 	}
	
	function TestExists()
	{
		$this->assertTrue(in_array('pgsql', pdo_drivers()));
	}
}