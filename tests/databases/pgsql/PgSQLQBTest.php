<?php
/**
 * OpenSQLManager
 *
 * Free Database manager for Open Source Databases
 *
 * @author 		Timothy J. Warren
 * @copyright	Copyright (c) 2012 - 2014
 * @link 		https://github.com/aviat4ion/OpenSQLManager
 * @license 	http://philsturgeon.co.uk/code/dbad-license
 */

// --------------------------------------------------------------------------

/**
 * @requires extension pdo_pgsql
 */
class PgSQLQBTest extends QBTest {

	public static function setUpBeforeClass()
	{
		$params = get_json_config();
		if (getenv('TRAVIS')) // Travis CI Connection Info
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
		// Attempt to connect, if there is a test config file
		else if ($params !== FALSE)
		{
			$params = $params->pgsql;
			$params->type = "pgsql";
			//$params->port = 5432;
			//$params->prefix = 'create_';
			$params->options = array();
			$params->options[\PDO::ATTR_PERSISTENT] = TRUE;
		}

		self::$db = Query($params);
	}

	public function setUp()
 	{
 		// If the database isn't installed, skip the tests
		if ( ! in_array('pgsql', PDO::getAvailableDrivers()))
		{
			$this->markTestSkipped("Postgres extension for PDO not loaded");
		}
 	}

 	// --------------------------------------------------------------------------

	public function testExists()
	{
		$this->assertTrue(in_array('pgsql', PDO::getAvailableDrivers()));
	}

	// --------------------------------------------------------------------------

	public function testQueryExplain()
	{
		if (getenv('CI'))
		{
			$this->markTestSkipped("Skip this test on CI, because the check is Postgres version dependent");
		}

		$query = self::$db->select('id, key as k, val')
			->explain()
			->where('id >', 1)
			->where('id <', 900)
			->get('create_test', 2, 1);

		$res = $query->fetchAll(PDO::FETCH_ASSOC);

		$expected = array (
		  array (
		    'QUERY PLAN' => 'Limit  (cost=6.31..10.54 rows=2 width=68)',
		  ),
		  array (
		    'QUERY PLAN' => '  Output: id, key, val',
		  ),
		  array (
		    'QUERY PLAN' => '  ->  Bitmap Heap Scan on public.create_test  (cost=4.19..12.66 rows=4 width=68)',
		  ),
		  array (
		    'QUERY PLAN' => '        Output: id, key, val',
		  ),
		  array (
		    'QUERY PLAN' => '        Recheck Cond: ((create_test.id > 1) AND (create_test.id < 900))',
		  ),
		  array (
		    'QUERY PLAN' => '        ->  Bitmap Index Scan on create_test_pkey  (cost=0.00..4.19 rows=4 width=0)',
		  ),
		  array (
		    'QUERY PLAN' => '              Index Cond: ((create_test.id > 1) AND (create_test.id < 900))',
		  ),
		);

		$this->assertEqual($expected, $res);
	}

	public function testBackupStructure()
	{
		$this->assertEquals('', self::$db->util->backup_structure());
	}
}