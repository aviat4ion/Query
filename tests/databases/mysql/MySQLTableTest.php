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
class MySQLTableTest extends TableBuilderTest {

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

			$this->db = new \Query\Driver\MySQL("mysql:host={$params->host};dbname={$params->database}", $params->user, $params->pass, array(
				PDO::ATTR_PERSISTENT => TRUE
			));
		}
		elseif (($var = getenv('CI')))
		{
			$this->db = new \Query\Driver\MySQL('host=127.0.0.1;port=3306;dbname=test', 'root');
		}

		$this->db->table_prefix = 'create_';
	}

}
// End of MySQLTableTest.php