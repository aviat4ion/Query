<?php declare(strict_types=1);
/**
 * Query
 *
 * SQL Query Builder / Database Abstraction Layer
 *
 * PHP version 7.1
 *
 * @package     Query
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2012 - 2018 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link        https://git.timshomepage.net/aviat4ion/Query
 */
namespace Query\Tests;

use BadMethodCallException;
use PDO;
use Query\Exception\BadDBDriverException;

/**
 * Query builder parent test class
 */
abstract class BaseQueryBuilderTest extends TestCase {

	/**
	 * @var \Query\QueryBuilderInterface|null
	 */
	protected static $db;

	public function __destruct()
	{
		if (isset($_GET['show_queries']))
		{
			echo '<pre>' . print_r(self::$db->queries, TRUE) . '</pre>';
		}
	}

	public static function tearDownAfterClass()
	{
		if (PHP_SAPI !== 'cli' && PHP_SAPI !== 'phpdbg')
		{
			echo '<pre>' . print_r(self::$db->queries, TRUE) . '</pre>';
		}

		self::$db = NULL;
	}

	// ! Driver-specific results
	abstract public function testQueryExplain();

	// ! Get tests
	public function testInvalidConnectionName()
	{
		$this->expectException('InvalidArgumentException');

		Query('foo');
	}

	public function testFunctionGet()
	{
		$query = self::$db->select('id, COUNT(id) as count')
			->from('test')
			->groupBy('id')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testGet()
	{
		$query = self::$db->get('test');

		$this->assertIsA($query, 'PDOStatement');
		$lastQuery = self::$db->getLastQuery();
		$this->assertTrue(\is_string($lastQuery));
	}

	public function testPrefixGet()
	{
		$query = self::$db->from('test')->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testGetWNumRows()
	{
		$query = self::$db->get('test');
		$numrows = count($query->fetchAll(PDO::FETCH_NUM));

		$this->assertEqual(self::$db->numRows(), $numrows);
	}

	public function testGetLimit()
	{
		$query = self::$db->get('test', 2);

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testGetLimitSkip()
	{
		$query = self::$db->get('test', 2, 1);

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testGetWhere()
	{
		$query = self::$db->getWhere('test', ['id !=' => 1], 2, 1);

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testHaving()
	{
		$query = self::$db->select('id')
			->from('test')
			->groupBy('id')
			->having(['id >' => 1])
			->having('id !=', 3)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testOrHaving()
	{
		$query = self::$db->select('id')
			->from('test')
			->groupBy('id')
			->having(['id >' => 1])
			->orHaving('id !=', 3)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}
	// ! Select tests
	public function testSelectWhereGet()
	{
		$query = self::$db->select('id, key as k, val')
			->where('id >', 1)
			->where('id <', 900)
			->get('test', 2, 1);

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testSelectAvg()
	{
		$query = self::$db->selectAvg('id', 'di')
			->get('test');

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testSelectSum()
	{
		$query = self::$db->selectSum('id', 'di')
			->get('test');

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testSelectDistinct()
	{
		$query = self::$db->selectSum('id', 'di')
			->distinct()
			->get('test');

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testSelectGet()
	{
		$query = self::$db->select('id, key as k, val')
			->get('test', 2, 1);

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testSelectFromGet()
	{
		$query = self::$db->select('id, key as k, val')
			->from('test ct')
			->where('id >', 1)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testSelectFromLimitGet()
	{
		$query = self::$db->select('id, key as k, val')
			->from('test ct')
			->where('id >', 1)
			->limit(3)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testSelectWhereGet2()
	{
		$query = self::$db->select('id, key as k, val')
			->where('id !=', 1)
			->get('test', 2, 1);

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testSelectMax()
	{
		$query = self::$db->selectMax('id', 'di')
			->get('test');

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testSelectMin()
	{
		$query = self::$db->selectMin('id', 'di')
			->get('test');

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testMultiOrderBy()
	{
		$query = self::$db->from('test')
			->orderBy('id, key')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}
	// ! Grouping tests
	public function testGroup()
	{
		$query = self::$db->select('id, key as k, val')
			->from('test')
			->groupStart()
			->where('id >', 1)
			->where('id <', 900)
			->groupEnd()
			->limit(2, 1)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testOrGroup()
	{
		$query = self::$db->select('id, key as k, val')
			->from('test')
			->groupStart()
			->where('id >', 1)
			->where('id <', 900)
			->groupEnd()
			->orGroupStart()
			->where('id =', 0)
			->groupEnd()
			->limit(2, 1)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testOrNotGroup()
	{
		$query = self::$db->select('id, key as k, val')
			->from('test')
			->groupStart()
			->where('id >', 1)
			->where('id <', 900)
			->groupEnd()
			->orNotGroupStart()
			->where('id =', 0)
			->groupEnd()
			->limit(2, 1)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testAndNotGroupStart()
	{
		$query = self::$db->select('id, key as k, val')
			->from('test')
			->groupStart()
			->where('id >', 1)
			->where('id <', 900)
			->groupEnd()
			->notGroupStart()
			->where('id =', 0)
			->groupEnd()
			->limit(2, 1)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testNotGroupStart()
	{
		$query = self::$db->select('id, key as k, val')
			->from('test')
			->notGroupStart()
			->where('id =', 0)
			->groupEnd()
			->limit(2, 1)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testGroupCamelCase()
	{
		$query = self::$db->select('id, key as k, val')
			->from('test')
			->groupStart()
			->where('id >', 1)
			->where('id <', 900)
			->groupEnd()
			->orNotGroupStart()
			->where('id =', 0)
			->groupEnd()
			->limit(2, 1)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}
	// ! Where In tests
	public function testWhereIn()
	{
		$query = self::$db->from('test')
			->whereIn('id', [0, 6, 56, 563, 341])
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testOrWhereIn()
	{
		$query = self::$db->from('test')
			->where('key', 'false')
			->orWhereIn('id', [0, 6, 56, 563, 341])
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testWhereNotIn()
	{
		$query = self::$db->from('test')
			->where('key', 'false')
			->whereNotIn('id', [0, 6, 56, 563, 341])
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testOrWhereNotIn()
	{
		$query = self::$db->from('test')
			->where('key', 'false')
			->orWhereNotIn('id', [0, 6, 56, 563, 341])
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}
	// ! Query modifier tests
	public function testOrderBy()
	{
		$query = self::$db->select('id, key as k, val')
			->from('test')
			->where('id >', 0)
			->where('id <', 9000)
			->orderBy('id', 'DESC')
			->orderBy('k', 'ASC')
			->limit(5,2)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testOrderByRandom()
	{
		$query = self::$db->select('id, key as k, val')
			->from('test')
			->where('id >', 0)
			->where('id <', 9000)
			->orderBy('id', 'rand')
			->limit(5,2)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testGroupBy()
	{
		$query = self::$db->select('id, key as k, val')
			->from('test')
			->where('id >', 0)
			->where('id <', 9000)
			->groupBy('k')
			->groupBy(['id','val'])
			->orderBy('id', 'DESC')
			->orderBy('k', 'ASC')
			->limit(5,2)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	//public function testOr

	public function testOrWhere()
	{
		$query = self::$db->select('id, key as k, val')
			->from('test')
			->where(' id ', 1)
			->orWhere('key >', 0)
			->limit(2, 1)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testLike()
	{
		$query = self::$db->from('test')
			->like('key', 'og')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testOrLike()
	{
		$query = self::$db->from('test')
			->like('key', 'og')
			->orLike('key', 'val')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testOrNotLike()
	{
		$query = self::$db->from('test')
			->like('key', 'og', 'before')
			->orNotLike('key', 'val')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testNotLike()
	{
		$query = self::$db->from('test')
			->like('key', 'og', 'before')
			->notLike('key', 'val')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testLikeBefore()
	{
		$query = self::$db->from('test')
			->like('key', 'og', 'before')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testLikeAfter()
	{
		$query = self::$db->from('test')
			->like('key', 'og', 'after')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testJoin()
	{
		$query = self::$db->from('test ct')
			->join('join cj', 'cj.id = ct.id')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testLeftJoin()
	{
		$query = self::$db->from('test ct')
			->join('join cj', 'cj.id = ct.id', 'left')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testInnerJoin()
	{
		$query = self::$db->from('test ct')
			->join('join cj', 'cj.id = ct.id', 'inner')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testJoinWithMultipleWhereValues()
	{
		$query = self::$db->from('test ct')
			->join('join cj', 'cj.id=ct.id', 'inner')
			->where([
				'ct.id < ' => 3,
				'ct.key' => 'foo'
			])
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// ! DB update tests
	public function testInsert()
	{
		$query = self::$db->set('id', 98)
			->set('key', 84)
			->set('val', 120)
			->insert('test');

		$this->assertIsA($query, 'PDOStatement');
		$this->assertTrue(self::$db->affectedRows() > 0);
	}

	public function testInsertArray()
	{
		$query = self::$db->insert('test', [
			'id' => 587,
			'key' => 1,
			'val' => 2,
		]);

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testInsertBatch()
	{
		$data = [
			[
				'id' => 544,
				'key' => 3,
				'val' => 7,
			],
			[
				'id' => 890,
				'key' => 34,
				'val' => "10 o'clock",
			],
			[
				'id' => 480,
				'key' => 403,
				'val' => 97,
			],
		];

		$query = self::$db->insertBatch('test', $data);

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testUpdate()
	{
		$query = self::$db->where('id', 7)
			->update('test', [
				'id' => 7,
				'key' => 'gogle',
				'val' => 'non-word'
			]);

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testUpdateBatchNull()
	{
		$query = self::$db->updateBatch('test', [], '');
		$this->assertNull($query);
	}

	public function testDriverUpdateBatch()
	{
		$data = [
			[
				'id' => 480,
				'key' => 49,
				'val' => '7x7'
			],
			[
				'id' => 890,
				'key' => 100,
				'val' => '10x10'
			]
		];

		$affectedRows = self::$db->updateBatch('test', $data, 'id');
		$this->assertEquals(2, $affectedRows);
	}

	public function testSetArrayUpdate()
	{
		$array = [
			'id' => 22,
			'key' => 'gogle',
			'val' => 'non-word'
		];

		$query = self::$db->set($array)
			->where('id', 22)
			->update('test');

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testWhereSetUpdate()
	{
		$query = self::$db->where('id', 36)
			->set('id', 36)
			->set('key', 'gogle')
			->set('val', 'non-word')
			->update('test');

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testDelete()
	{
		$query = self::$db->delete('test', ['id' => 5]);

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testDeleteWithMultipleWhereValues()
	{
		$query = self::$db->delete('test', [
			'id' => 5,
			'key' => 'gogle'
		]);

		$this->assertIsA($query, 'PDOStatement');
	}

	// ! Non-data read queries
	public function testCountAll()
	{
		$query = self::$db->countAll('test');

		$this->assertTrue(is_numeric($query));
	}

	public function testCountAllResults()
	{
		$query = self::$db->countAllResults('test');

		$this->assertTrue(is_numeric($query));
	}

	public function testCountAllResults2()
	{
		$query = self::$db->select('id, key as k, val')
			->from('test')
			->where(' id ', 1)
			->orWhere('key >', 0)
			->limit(2, 1)
			->countAllResults();

		$this->assertTrue(is_numeric($query));
	}

	public function testNumRows()
	{
		self::$db->get('test');
		$this->assertTrue(is_numeric(self::$db->numRows()));
	}

	// ! Compiled Query tests
	public function testGetCompiledSelect()
	{
		$sql = self::$db->getCompiledSelect('test');
		$qbRes = self::$db->get('test');
		$sqlRes = self::$db->query($sql);

		$this->assertIsA($qbRes,'PDOStatement', "Query Builder Result is a PDO Statement");
		$this->assertIsA($sqlRes, 'PDOStatement', "SQL Result is a PDO Statement");
		//$this->assertEquals($qbRes, $sqlRes);
	}

	public function testGetCompiledUpdate()
	{
		$sql = self::$db->set([
			'id' => 4,
			'key' => 'foo',
			'val' => 'baz'
		])->getCompiledUpdate('test');

		$this->assertTrue(\is_string($sql));
	}

	public function testGetCompiledInsert()
	{
		$sql = self::$db->set([
			'id' => 4,
			'key' => 'foo',
			'val' => 'baz'
		])->getCompiledInsert('test');

		$this->assertTrue(\is_string($sql));
	}

	public function testGetCompiledDelete()
	{
		$sql = self::$db->where('id', 4)
			->getCompiledDelete('test');

		$this->assertTrue(\is_string($sql));
	}
	// ! Error tests
	/**
	 * Handles invalid drivers
	 */
	public function testBadDriver()
	{
		$params = [
			'host' => '127.0.0.1',
			'port' => '3306',
			'database' => 'test',
			'user' => 'root',
			'pass' => NULL,
			'type' => 'QGYFHGEG'
		];

		$this->expectException(BadDBDriverException::class);

		self::$db = Query($params);
	}

	public function testBadMethod()
	{
		$this->expectException(BadMethodCallException::class);

		self::$db->foo();
	}

	public function testBadNumRows()
	{
		self::$db->set([
			'id' => 999,
			'key' => 'ring',
			'val' => 'sale'
		])->insert('test');

		$res = self::$db->numRows();
		$this->assertEqual(NULL, $res);
	}
}