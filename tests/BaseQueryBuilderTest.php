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

namespace Query\Tests;

use BadMethodCallException;
use PDO;
use Query\Exception\BadDBDriverException;
use Query\QueryBuilderInterface;

/**
 * Query builder parent test class
 */
abstract class BaseQueryBuilderTest extends BaseTestCase
{
	/**
	 * @var QueryBuilderInterface|null
	 */
	protected static $db;

	public function __destruct()
	{
		if (isset($_GET['show_queries']))
		{
			echo '<pre>' . print_r(self::$db->queries, TRUE) . '</pre>';
		}
	}

	public static function tearDownAfterClass(): void
	{
		if (PHP_SAPI !== 'cli' && PHP_SAPI !== 'phpdbg')
		{
			echo '<pre>' . print_r(self::$db->queries, TRUE) . '</pre>';
		}

		self::$db = NULL;
	}

	// ! Driver-specific results
	abstract public function testQueryExplain(): void;

	// ! Get tests
	public function testInvalidConnectionName(): void
	{
		$this->expectException('InvalidArgumentException');

		Query('foo');
	}

	public function testFunctionGet(): void
	{
		$query = self::$db->select('id, COUNT(id) as count')
			->from('test')
			->groupBy('id')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testGet(): void
	{
		$query = self::$db->get('test');

		$this->assertIsA($query, 'PDOStatement');
		$lastQuery = self::$db->getLastQuery();
		$this->assertIsString($lastQuery);
	}

	public function testPrefixGet(): void
	{
		$query = self::$db->from('test')->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testGetWNumRows(): void
	{
		$query = self::$db->get('test');
		$numrows = count($query->fetchAll(PDO::FETCH_NUM));

		$this->assertEquals(self::$db->numRows(), $numrows);
	}

	public function testGetLimit(): void
	{
		$query = self::$db->get('test', 2);

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testGetLimitSkip(): void
	{
		$query = self::$db->get('test', 2, 1);

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testGetWhere(): void
	{
		$query = self::$db->getWhere('test', ['id !=' => 1], 2, 1);

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testHaving(): void
	{
		$query = self::$db->select('id')
			->from('test')
			->groupBy('id')
			->having(['id >' => 1])
			->having('id !=', 3)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testOrHaving(): void
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
	public function testSelectWhereGet(): void
	{
		$query = self::$db->select('id, key as k, val')
			->where('id >', 1)
			->where('id <', 900)
			->get('test', 2, 1);

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testSelectWhereGetNoAs(): void
	{
		$query = self::$db->select('id, key, val')
			->where('id >', 1)
			->where('id <', 900)
			->get('test', 2, 1);

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testSelectAvg(): void
	{
		$query = self::$db->selectAvg('id', 'di')
			->get('test');

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testSelectSum(): void
	{
		$query = self::$db->selectSum('id', 'di')
			->get('test');

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testSelectDistinct(): void
	{
		$query = self::$db->selectSum('id', 'di')
			->distinct()
			->get('test');

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testSelectGet(): void
	{
		$query = self::$db->select('id, key as k, val')
			->get('test', 2, 1);

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testSelectFromGet(): void
	{
		$query = self::$db->select('id, key as k, val')
			->from('test ct')
			->where('id >', 1)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testSelectTableGet(): void
	{
		$query = self::$db->select('id, key as k, val')
			->table('test ct')
			->where('id >', 1)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testSelectFromLimitGet(): void
	{
		$query = self::$db->select('id, key as k, val')
			->from('test ct')
			->where('id >', 1)
			->limit(3)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testSelectWhereGet2(): void
	{
		$query = self::$db->select('id, key as k, val')
			->where('id !=', 1)
			->get('test', 2, 1);

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testSelectMax(): void
	{
		$query = self::$db->selectMax('id', 'di')
			->get('test');

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testSelectMin(): void
	{
		$query = self::$db->selectMin('id', 'di')
			->get('test');

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testMultiOrderBy(): void
	{
		$query = self::$db->from('test')
			->orderBy('id, key')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// ! Grouping tests
	public function testGroup(): void
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

	public function testOrGroup(): void
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

	public function testOrNotGroup(): void
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

	public function testAndNotGroupStart(): void
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

	public function testNotGroupStart(): void
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

	public function testGroupCamelCase(): void
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
	public function testWhereIn(): void
	{
		$query = self::$db->from('test')
			->whereIn('id', [0, 6, 56, 563, 341])
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testOrWhereIn(): void
	{
		$query = self::$db->from('test')
			->where('key', 'false')
			->orWhereIn('id', [0, 6, 56, 563, 341])
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testWhereNotIn(): void
	{
		$query = self::$db->from('test')
			->where('key', 'false')
			->whereNotIn('id', [0, 6, 56, 563, 341])
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testOrWhereNotIn(): void
	{
		$query = self::$db->from('test')
			->where('key', 'false')
			->orWhereNotIn('id', [0, 6, 56, 563, 341])
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// ! Query modifier tests
	public function testOrderBy(): void
	{
		$query = self::$db->select('id, key as k, val')
			->from('test')
			->where('id >', 0)
			->where('id <', 9000)
			->orderBy('id', 'DESC')
			->orderBy('k', 'ASC')
			->limit(5, 2)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testOrderByRandom(): void
	{
		$query = self::$db->select('id, key as k, val')
			->from('test')
			->where('id >', 0)
			->where('id <', 9000)
			->orderBy('id', 'rand')
			->limit(5, 2)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testGroupBy(): void
	{
		$query = self::$db->select('id, key as k, val')
			->from('test')
			->where('id >', 0)
			->where('id <', 9000)
			->groupBy('k')
			->groupBy(['id', 'val'])
			->orderBy('id', 'DESC')
			->orderBy('k', 'ASC')
			->limit(5, 2)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	//public function testOr

	public function testOrWhere(): void
	{
		$query = self::$db->select('id, key as k, val')
			->from('test')
			->where(' id ', 1)
			->orWhere('key >', 0)
			->limit(2, 1)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testLike(): void
	{
		$query = self::$db->from('test')
			->like('key', 'og')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testOrLike(): void
	{
		$query = self::$db->from('test')
			->like('key', 'og')
			->orLike('key', 'val')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testOrNotLike(): void
	{
		$query = self::$db->from('test')
			->like('key', 'og', 'before')
			->orNotLike('key', 'val')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testNotLike(): void
	{
		$query = self::$db->from('test')
			->like('key', 'og', 'before')
			->notLike('key', 'val')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testLikeBefore(): void
	{
		$query = self::$db->from('test')
			->like('key', 'og', 'before')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testLikeAfter(): void
	{
		$query = self::$db->from('test')
			->like('key', 'og', 'after')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testJoin(): void
	{
		$query = self::$db->from('test ct')
			->join('join cj', 'cj.id = ct.id')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testLeftJoin(): void
	{
		$query = self::$db->from('test ct')
			->join('join cj', 'cj.id = ct.id', 'left')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testInnerJoin(): void
	{
		$query = self::$db->from('test ct')
			->join('join cj', 'cj.id = ct.id', 'inner')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testJoinWithMultipleWhereValues(): void
	{
		$query = self::$db->from('test ct')
			->join('join cj', 'cj.id=ct.id', 'inner')
			->where([
				'ct.id < ' => 3,
				'ct.key' => 'foo',
			])
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// ! DB update tests
	public function testInsert(): void
	{
		$query = self::$db->set('id', 98)
			->set('key', 84)
			->set('val', 120)
			->insert('test');

		$this->assertIsA($query, 'PDOStatement');
		$this->assertTrue(self::$db->affectedRows() > 0);
	}

	public function testInsertArray(): void
	{
		$query = self::$db->insert('test', [
			'id' => 587,
			'key' => 1,
			'val' => 2,
		]);

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testInsertReturning(): void
	{
		$query = self::$db->set('id', 99)
			->set('key', 84)
			->set('val', 120)
			->returning()
			->insert('test');

		$row = $query->fetch(PDO::FETCH_ASSOC);

		$this->assertIsA($query, 'PDOStatement');
		$this->assertEquals([
			'id' => 99,
			'key' => 84,
			'val' => 120,
		], $row);
	}

	public function testInsertBatch(): void
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

	public function testUpdate(): void
	{
		$query = self::$db->where('id', 7)
			->update('test', [
				'id' => 7,
				'key' => 'gogle',
				'val' => 'non-word',
			]);

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testUpdateReturning(): void
	{
		// Make sure the id exists so there isn't an error for that reason
		self::$db->insert('test', [
			'id' => 7,
			'key' => 'kjsgarik yugasdikh',
			'val' => 'alkfiasghdfdhs',
		]);

		$query = self::$db->where('id', 7)
			->set([
				'id' => 7,
				'key' => 'gogle',
				'val' => 'non-word',
			])
			->returning('key')
			->update('test');

		$this->assertIsA($query, 'PDOStatement');
		$row = $query->fetch(PDO::FETCH_ASSOC);
		$this->assertEquals([
			'key' => 'gogle',
		], $row, json_encode($query->errorInfo()));

		// $this->assertNotEqual(FALSE, $row, $query->errorInfo());
	}

	public function testUpdateBatchNull(): void
	{
		$query = self::$db->updateBatch('test', [], '');
		$this->assertNull($query);
	}

	public function testDriverUpdateBatch(): void
	{
		$data = [
			[
				'id' => 480,
				'key' => 49,
				'val' => '7x7',
			],
			[
				'id' => 890,
				'key' => 100,
				'val' => '10x10',
			],
		];

		$affectedRows = self::$db->updateBatch('test', $data, 'id');
		$this->assertEquals(2, $affectedRows);
	}

	public function testSetArrayUpdate(): void
	{
		$array = [
			'id' => 22,
			'key' => 'gogle',
			'val' => 'non-word',
		];

		$query = self::$db->set($array)
			->where('id', 22)
			->update('test');

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testWhereSetUpdate(): void
	{
		$query = self::$db->where('id', 36)
			->set('id', 36)
			->set('key', 'gogle')
			->set('val', 'non-word')
			->update('test');

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testDelete(): void
	{
		$query = self::$db->delete('test', ['id' => 5]);

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testDeleteReturning(): void
	{
		$query = self::$db->returning()->delete('test', ['id' => 99]);

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testDeleteWithMultipleWhereValues(): void
	{
		$query = self::$db->delete('test', [
			'id' => 5,
			'key' => 'gogle',
		]);

		$this->assertIsA($query, 'PDOStatement');
	}

	// ! Non-data read queries
	public function testCountAll(): void
	{
		$query = self::$db->countAll('test');

		$this->assertIsNumeric($query);
	}

	public function testCountAllResults(): void
	{
		$query = self::$db->countAllResults('test');

		$this->assertIsNumeric($query);
	}

	public function testCountAllResults2(): void
	{
		$query = self::$db->select('id, key as k, val')
			->from('test')
			->where(' id ', 1)
			->orWhere('key >', 0)
			->limit(2, 1)
			->countAllResults();

		$this->assertIsNumeric($query);
	}

	public function testNumRows(): void
	{
		self::$db->get('test');
		$this->assertIsNumeric(self::$db->numRows());
	}

	// ! Compiled Query tests
	public function testGetCompiledSelect(): void
	{
		$sql = self::$db->getCompiledSelect('test');
		$qbRes = self::$db->get('test');
		$sqlRes = self::$db->query($sql);

		$this->assertIsA($qbRes, 'PDOStatement', 'Query Builder Result is a PDO Statement');
		$this->assertIsA($sqlRes, 'PDOStatement', 'SQL Result is a PDO Statement');
		//$this->assertEquals($qbRes, $sqlRes);
	}

	public function testGetCompiledUpdate(): void
	{
		$sql = self::$db->set([
			'id' => 4,
			'key' => 'foo',
			'val' => 'baz',
		])->getCompiledUpdate('test');

		$this->assertIsString($sql);
	}

	public function testGetCompiledInsert(): void
	{
		$sql = self::$db->set([
			'id' => 4,
			'key' => 'foo',
			'val' => 'baz',
		])->getCompiledInsert('test');

		$this->assertIsString($sql);
	}

	public function testGetCompiledDelete(): void
	{
		$sql = self::$db->where('id', 4)
			->getCompiledDelete('test');

		$this->assertIsString($sql);
	}

	// ! Error tests
	/**
	 * Handles invalid drivers
	 */
	public function testBadDriver(): void
	{
		$params = [
			'host' => '127.0.0.1',
			'port' => '3306',
			'database' => 'test',
			'user' => 'root',
			'pass' => NULL,
			'type' => 'QGYFHGEG',
		];

		$this->expectException(BadDBDriverException::class);

		self::$db = Query($params);
	}

	public function testBadMethod(): void
	{
		$this->expectException(BadMethodCallException::class);

		self::$db->foo();
	}

	public function testBadNumRows(): void
	{
		self::$db->set([
			'id' => 999,
			'key' => 'ring',
			'val' => 'sale',
		])->insert('test');

		$res = self::$db->numRows();
		$this->assertNull($res);
	}
}
