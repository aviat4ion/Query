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

class PgSQLQBTest extends QBTest {

	public function __construct()
 	{
 		parent::__construct();

 		// Attempt to connect, if there is a test config file
		if (is_file(QBASE_DIR . "test_config.json"))
		{
			$params = json_decode(file_get_contents(QBASE_DIR . "test_config.json"));
			$params = $params->pgsql;
			$params->type = "pgsql";
			$params->prefix = 'create_';
		}
		elseif (($var = getenv('CI')))
		{
			$params = array(
				'host' => '127.0.0.1',
				'port' => '5432',
				'database' => 'test',
				'user' => 'postgres',
				'pass' => '',
				'type' => 'pgsql',
				'prefix' => 'create_'
			);
		}

		$this->db = Query($params);
 	}

 	// --------------------------------------------------------------------------

	public function TestExists()
	{
		$this->assertTrue(in_array('pgsql', PDO::getAvailableDrivers()));
	}
}