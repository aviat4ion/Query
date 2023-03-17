<?php declare(strict_types=1);
/**
 * Query
 *
 * SQL Query Builder / Database Abstraction Layer
 *
 * PHP version 8.1
 *
 * @package     Query
 * @author      Timothy J. Warren <tim@timshome.page>
 * @copyright   2012 - 2023 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link        https://git.timshomepage.net/aviat/Query
 * @version     4.0.0
 */

namespace Query\Tests\Drivers\PgSQL;

use PDO;
use Query\Tests\BaseQueryBuilderTest;
use function in_array;

/**
 * @requires extension pdo_pgsql
 */
class PgSQLQueryBuilderTest extends BaseQueryBuilderTest
{
	public static function setUpBeforeClass(): void
	{
		$params = get_json_config();
		if (getenv('TRAVIS')) // Travis CI Connection Info
		{
			$params = [
				'host' => '127.0.0.1',
				'port' => '5432',
				'database' => 'test',
				'user' => 'postgres',
				'pass' => '',
				'type' => 'pgsql',
				'prefix' => 'create_',
			];
		}
		// Attempt to connect, if there is a test config file
		elseif ($params !== FALSE)
		{
			$params = $params->pgsql;
			$params->type = 'pgsql';
			//$params->port = 5432;
			//$params->prefix = 'create_';
			$params->options = [];
			$params->options[PDO::ATTR_PERSISTENT] = TRUE;
		}

		self::$db = Query($params);
	}

	protected function setUp(): void
	{
		// If the database isn't installed, skip the tests
		if ( ! in_array('pgsql', PDO::getAvailableDrivers(), TRUE))
		{
			$this->markTestSkipped('Postgres extension for PDO not loaded');
		}
	}

	public function testExists(): void
	{
		$this->assertTrue(in_array('pgsql', PDO::getAvailableDrivers(), TRUE));
	}

	public function testQueryExplain(): void
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
		$this->assertIsArray($res);
		$this->assertTrue(count($res) > 1);
		$this->assertArrayHasKey('QUERY PLAN', $res[0]);

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

	public function testBackupStructure(): void
	{
		$this->assertEquals('', self::$db->getUtil()->backupStructure());
	}
}
