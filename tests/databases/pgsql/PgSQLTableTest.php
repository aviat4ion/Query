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
 * Parent Table Builder Test Class
 */
class PgSQLTableTest extends TableBuilderTest {

	public function setUp()
	{
		$class = "\\Query\\Driver\\PgSQL";

		// If the database isn't installed, skip the tests
		if ( ! class_exists($class))
		{
			$this->markTestSkipped("Postgres extension for PDO not loaded");
		}

		// Attempt to connect, if there is a test config file
		if (is_file(QTEST_DIR . "/settings.json"))
		{
			$params = json_decode(file_get_contents(QTEST_DIR . "/settings.json"));
			$params = $params->pgsql;

			$this->db = new $class("pgsql:dbname={$params->database}", $params->user, $params->pass);
		}
		elseif (($var = getenv('CI')))
		{
			$this->db = new $class('host=127.0.0.1;port=5432;dbname=test', 'postgres');
		}

		$this->db->table_prefix = 'create_';
	}

}
// End of PgSQLTableTest.php