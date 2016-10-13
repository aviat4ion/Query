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
 * Query builder parent test class
 */
abstract class QBTest extends Query_TestCase {

	protected static $db;

	public function __destruct()
	{
		if (isset($_GET['show_queries']))
		{
			echo '<pre>' . print_r(self::$db->queries, TRUE) . '</pre>';
		}
	}

	// --------------------------------------------------------------------------

	public static function tearDownAfterClass()
	{
		self::$db = NULL;
	}

	// --------------------------------------------------------------------------
	// ! Driver-specific results
	// --------------------------------------------------------------------------

	abstract public function testQueryExplain();

	// --------------------------------------------------------------------------
	// ! Get tests
	// --------------------------------------------------------------------------

	public function testInvalidConnectionName()
	{
		try
		{
			$db = Query('foo');
		}
		catch (InvalidArgumentException $e)
		{
			$this->assertIsA($e, 'InvalidArgumentException');
		}
	}

	// --------------------------------------------------------------------------

	public function testFunctionGet()
	{
		$query = self::$db->select('id, COUNT(id) as count')
			->from('test')
			->group_by('id')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testGet()
	{
		$query = self::$db->get('test');

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testPrefixGet()
	{
		$query = self::$db->from('test')->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testGetWNumRows()
	{
		$query = self::$db->get('test');
		$numrows = count($query->fetchAll(PDO::FETCH_NUM));

		$this->assertEqual(self::$db->num_rows(), $numrows);
	}

	// --------------------------------------------------------------------------

	public function testGetLimit()
	{
		$query = self::$db->get('test', 2);

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testGetLimitSkip()
	{
		$query = self::$db->get('test', 2, 1);

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testGetWhere()
	{
		$query = self::$db->get_where('test', array('id !=' => 1), 2, 1);

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testHaving()
	{
		$query = self::$db->select('id')
			->from('test')
			->group_by('id')
			->having(array('id >' => 1))
			->having('id !=', 3)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testOrHaving()
	{
		$query = self::$db->select('id')
			->from('test')
			->group_by('id')
			->having(array('id >' => 1))
			->or_having('id !=', 3)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------
	// ! Select tests
	// --------------------------------------------------------------------------

	public function testSelectWhereGet()
	{
		$query = self::$db->select('id, key as k, val')
			->where('id >', 1)
			->where('id <', 900)
			->get('test', 2, 1);

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testSelectAvg()
	{
		$query = self::$db->select_avg('id', 'di')
			->get('test');

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testSelectSum()
	{
		$query = self::$db->select_sum('id', 'di')
			->get('test');

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testSelectDistinct()
	{
		$query = self::$db->select_sum('id', 'di')
			->distinct()
			->get('test');

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testSelectGet()
	{
		$query = self::$db->select('id, key as k, val')
			->get('test', 2, 1);

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testSelectFromGet()
	{
		$query = self::$db->select('id, key as k, val')
			->from('test ct')
			->where('id >', 1)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testSelectFromLimitGet()
	{
		$query = self::$db->select('id, key as k, val')
			->from('test ct')
			->where('id >', 1)
			->limit(3)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}


	// --------------------------------------------------------------------------

	public function testSelectWhereGet2()
	{
		$query = self::$db->select('id, key as k, val')
			->where('id !=', 1)
			->get('test', 2, 1);

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testSelectMax()
	{
		$query = self::$db->select_max('id', 'di')
			->get('test');

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testSelectMin()
	{
		$query = self::$db->select_min('id', 'di')
			->get('test');

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testMultiOrderBy()
	{
		$query = self::$db->from('test')
			->order_by('id, key')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------
	// ! Grouping tests
	// --------------------------------------------------------------------------

	public function testGroup()
	{
		$query = self::$db->select('id, key as k, val')
			->from('test')
			->group_start()
			->where('id >', 1)
			->where('id <', 900)
			->group_end()
			->limit(2, 1)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testOrGroup()
	{
		$query = self::$db->select('id, key as k, val')
			->from('test')
			->group_start()
			->where('id >', 1)
			->where('id <', 900)
			->group_end()
			->or_group_start()
			->where('id =', 0)
			->group_end()
			->limit(2, 1)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testOrNotGroup()
	{
		$query = self::$db->select('id, key as k, val')
			->from('test')
			->group_start()
			->where('id >', 1)
			->where('id <', 900)
			->group_end()
			->or_not_group_start()
			->where('id =', 0)
			->group_end()
			->limit(2, 1)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testAndNotGroupStart()
	{
		$query = self::$db->select('id, key as k, val')
			->from('test')
			->group_start()
			->where('id >', 1)
			->where('id <', 900)
			->group_end()
			->not_group_start()
			->where('id =', 0)
			->group_end()
			->limit(2, 1)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testNotGroupStart()
	{
		$query = self::$db->select('id, key as k, val')
			->from('test')
			->not_group_start()
			->where('id =', 0)
			->group_end()
			->limit(2, 1)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

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

	// --------------------------------------------------------------------------
	// ! Where In tests
	// --------------------------------------------------------------------------

	public function testWhereIn()
	{
		$query = self::$db->from('test')
			->where_in('id', array(0, 6, 56, 563, 341))
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testOrWhereIn()
	{
		$query = self::$db->from('test')
			->where('key', 'false')
			->or_where_in('id', array(0, 6, 56, 563, 341))
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testWhereNotIn()
	{
		$query = self::$db->from('test')
			->where('key', 'false')
			->where_not_in('id', array(0, 6, 56, 563, 341))
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testOrWhereNotIn()
	{
		$query = self::$db->from('test')
			->where('key', 'false')
			->or_where_not_in('id', array(0, 6, 56, 563, 341))
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------
	// ! Query modifier tests
	// --------------------------------------------------------------------------

	public function testOrderBy()
	{
		$query = self::$db->select('id, key as k, val')
			->from('test')
			->where('id >', 0)
			->where('id <', 9000)
			->order_by('id', 'DESC')
			->order_by('k', 'ASC')
			->limit(5,2)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testOrderByRandom()
	{
		$query = self::$db->select('id, key as k, val')
			->from('test')
			->where('id >', 0)
			->where('id <', 9000)
			->order_by('id', 'rand')
			->limit(5,2)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testGroupBy()
	{
		$query = self::$db->select('id, key as k, val')
			->from('test')
			->where('id >', 0)
			->where('id <', 9000)
			->group_by('k')
			->group_by(array('id','val'))
			->order_by('id', 'DESC')
			->order_by('k', 'ASC')
			->limit(5,2)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	//public function testOr

	// --------------------------------------------------------------------------

	public function testOrWhere()
	{
		$query = self::$db->select('id, key as k, val')
			->from('test')
			->where(' id ', 1)
			->or_where('key >', 0)
			->limit(2, 1)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testLike()
	{
		$query = self::$db->from('test')
			->like('key', 'og')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testOrLike()
	{
		$query = self::$db->from('test')
			->like('key', 'og')
			->or_like('key', 'val')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testOrNotLike()
	{
		$query = self::$db->from('test')
			->like('key', 'og', 'before')
			->or_not_like('key', 'val')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testNotLike()
	{
		$query = self::$db->from('test')
			->like('key', 'og', 'before')
			->not_like('key', 'val')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testLikeBefore()
	{
		$query = self::$db->from('test')
			->like('key', 'og', 'before')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testLikeAfter()
	{
		$query = self::$db->from('test')
			->like('key', 'og', 'after')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testJoin()
	{
		$query = self::$db->from('test ct')
			->join('join cj', 'cj.id = ct.id')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testLeftJoin()
	{
		$query = self::$db->from('test ct')
			->join('join cj', 'cj.id = ct.id', 'left')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testInnerJoin()
	{
		$query = self::$db->from('test ct')
			->join('join cj', 'cj.id = ct.id', 'inner')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testJoinWithMultipleWhereValues()
	{
		$query = self::$db->from('test ct')
			->join('join cj', 'cj.id=ct.id', 'inner')
			->where(array(
				'ct.id < ' => 3,
				'ct.key' => 'foo'
			))
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------
	// ! DB update tests
	// --------------------------------------------------------------------------

	public function testInsert()
	{
		$query = self::$db->set('id', 98)
			->set('key', 84)
			->set('val', 120)
			->insert('test');

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testInsertArray()
	{
		$query = self::$db->insert('test', array(
				'id' => 587,
				'key' => 1,
				'val' => 2,
			));

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testInsertBatch()
	{
		$data = array(
			array(
				'id' => 544,
				'key' => 3,
				'val' => 7,
			),
			array(
				'id' => 89,
				'key' => 34,
				'val' => "10 o'clock",
			),
			array(
				'id' => 48,
				'key' => 403,
				'val' => 97,
			),
		);

		$query = self::$db->insert_batch('test', $data);

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testUpdate()
	{
		$query = self::$db->where('id', 7)
			->update('test', array(
				'id' => 7,
				'key' => 'gogle',
				'val' => 'non-word'
			));

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testSetArrayUpdate()
	{
		$array = array(
			'id' => 22,
			'key' => 'gogle',
			'val' => 'non-word'
		);

		$query = self::$db->set($array)
			->where('id', 22)
			->update('test');

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testWhereSetUpdate()
	{
		$query = self::$db->where('id', 36)
			->set('id', 36)
			->set('key', 'gogle')
			->set('val', 'non-word')
			->update('test');

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testDelete()
	{
		$query = self::$db->delete('test', array('id' => 5));

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testDeleteWithMultipleWhereValues()
	{
		$query = self::$db->delete('test', array(
			'id' => 5,
			'key' => 'gogle'
		));

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------
	// ! Non-data read queries
	// --------------------------------------------------------------------------

	public function testCountAll()
	{
		$query = self::$db->count_all('test');

		$this->assertTrue(is_numeric($query));
	}

	// --------------------------------------------------------------------------

	public function testCountAllResults()
	{
		$query = self::$db->count_all_results('test');

		$this->assertTrue(is_numeric($query));
	}

	// --------------------------------------------------------------------------

	public function testCountAllResults2()
	{
		$query = self::$db->select('id, key as k, val')
			->from('test')
			->where(' id ', 1)
			->or_where('key >', 0)
			->limit(2, 1)
			->count_all_results();

		$this->assertTrue(is_numeric($query));
	}

	// --------------------------------------------------------------------------

	public function testNumRows()
	{
		$query = self::$db->get('test');

		$this->assertTrue(is_numeric(self::$db->num_rows()));
	}

	// --------------------------------------------------------------------------
	// ! Compiled Query tests
	// --------------------------------------------------------------------------

	public function testGetCompiledSelect()
	{
		$sql = self::$db->get_compiled_select('test');
		$qb_res = self::$db->get('test');
		$sql_res = self::$db->query($sql);

		$this->assertIsA($qb_res,'PDOStatement', "Query Builder Result is a PDO Statement");
		$this->assertIsA($sql_res, 'PDOStatement', "SQL Result is a PDO Statement");
		//$this->assertEquals($qb_res, $sql_res);
	}

	public function testGetCompiledUpdate()
	{
		$sql = self::$db->set(array(
			'id' => 4,
			'key' => 'foo',
			'val' => 'baz'
		))->get_compiled_update('test');

		$this->assertTrue(is_string($sql));
	}

	public function testGetCompiledInsert()
	{
		$sql = self::$db->set(array(
			'id' => 4,
			'key' => 'foo',
			'val' => 'baz'
		))->get_compiled_insert('test');

		$this->assertTrue(is_string($sql));
	}

	public function testGetCompiledDelete()
	{
		$sql = self::$db->where('id', 4)
			->get_compiled_delete('test');

		$this->assertTrue(is_string($sql));
	}

	// --------------------------------------------------------------------------
	// ! Error tests
	// --------------------------------------------------------------------------

	/**
	 * Handles invalid drivers
	 */
	public function testBadDriver()
	{
		$params = array(
			'host' => '127.0.0.1',
			'port' => '3306',
			'database' => 'test',
			'user' => 'root',
			'pass' => NULL,
			'type' => 'QGYFHGEG'
		);

		try
		{
			self::$db = Query($params);
		}
		catch(\Query\BadDBDriverException $e)
		{
			$this->assertInstanceOf('\\Query\\BadDBDriverException', $e);
		}
	}

	// --------------------------------------------------------------------------

	public function testBadMethod()
	{
		try
		{
			self::$db->foo();
		}
		catch(BadMethodCallException $e)
		{
			$this->assertInstanceOf('BadMethodCallException', $e);
		}
	}

	// --------------------------------------------------------------------------

	public function testBadNumRows()
	{
		self::$db->set(array(
			'id' => 999,
			'key' => 'ring',
			'val' => 'sale'
		))->insert('test');

		$res = self::$db->num_rows();
		$this->assertEqual(NULL, $res);
	}
}

// End of db_qb_test.php