<?php declare(strict_types=1);
/**
 * Query
 *
 * SQL Query Builder / Database Abstraction Layer
 *
 * PHP version 7
 *
 * @package     Query
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2012 - 2016 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link        https://git.timshomepage.net/aviat4ion/Query
 */

namespace Query\Tests\Drivers\PgSQL;

use PDO;
use Query\Tests\BaseQueryBuilderTest;

// --------------------------------------------------------------------------

/**
 * @requires extension pdo_pgsql
 */
class PgSQLQueryBuilderTest extends BaseQueryBuilderTest {

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
		$query = self::$db->select('id, key as k, val')
			->explain()
			->where('id >', 1)
			->where('id <', 900)
			->get('create_test', 2, 1);

		$res = $query->fetchAll(PDO::FETCH_ASSOC);

		// The exact results are version dependent
		// The important thing is that there is an array
		// of results returned
		$this->assertTrue(is_array($res));
		$this->assertTrue(count($res) > 1);
		$this->assertTrue(array_key_exists('QUERY PLAN', $res[0]));

		/*$expected = array (
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

		$this->assertEqual($expected, $res);*/
	}

	public function testBackupStructure()
	{
		$this->assertEquals('', self::$db->util->backupStructure());
	}
}