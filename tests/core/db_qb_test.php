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

	public function __destruct()
	{
		if (isset($_GET['show_queries']))
		{
			echo '<pre>' . print_r($this->db->queries, TRUE) . '</pre>';
		}
	}

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

	public function testQueryFunctionAlias()
	{
		$db = Query();

		$this->assertTrue($this->db === $db);
	}

	// --------------------------------------------------------------------------

	public function testFunctionGet()
	{
		$query = $this->db->select('id, COUNT(id) as count')
			->from('test')
			->group_by('id')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testGet()
	{
		$query = $this->db->get('test');

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testPrefixGet()
	{
		$query = $this->db->from('test')->get();

		$this->assertIsA($query, 'PDOStatement');
		$this->assertTrue($this->db->num_rows() > 0);
	}

	// --------------------------------------------------------------------------

	public function testGetWNumRows()
	{
		$query = $this->db->get('test');
		$numrows = count($query->fetchAll(PDO::FETCH_NUM));

		$this->assertEqual($this->db->num_rows(), $numrows);
	}

	// --------------------------------------------------------------------------

	public function testGetLimit()
	{
		$query = $this->db->get('test', 2);

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testGetLimitSkip()
	{
		$query = $this->db->get('test', 2, 1);

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testGetWhere()
	{
		$query = $this->db->get_where('test', array('id !=' => 1), 2, 1);

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testHaving()
	{
		$query = $this->db->select('id')
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
		$query = $this->db->select('id')
			->from('test')
			->group_by('id')
			->having(array('id >' => 1))
			->or_having('id !=', 3)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testGetViews()
	{
		$this->assertTrue(is_array($this->db->get_views()));
	}

	// --------------------------------------------------------------------------
	// ! Select tests
	// --------------------------------------------------------------------------

	public function testSelectWhereGet()
	{
		$query = $this->db->select('id, key as k, val')
			->where('id >', 1)
			->where('id <', 900)
			->get('test', 2, 1);

		$this->assertIsA($query, 'PDOStatement');
	}



	// --------------------------------------------------------------------------

	public function testSelectWhereGet2()
	{
		$query = $this->db->select('id, key as k, val')
			->where('id !=', 1)
			->get('test', 2, 1);

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testSelectMax()
	{
		$query = $this->db->select_max('id', 'di')
			->get('test');

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testSelectMin()
	{
		$query = $this->db->select_min('id', 'di')
			->get('test');

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testMultiOrderBy()
	{
		$query = $this->db->from('test')
			->order_by('id, key')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------
	// ! Grouping tests
	// --------------------------------------------------------------------------

	public function testGroup()
	{
		$query = $this->db->select('id, key as k, val')
			->from('test')
			->group_start()
			->where('id >', 1)
			->where('id <', 900)
			->group_end()
			->limit(2, 1)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	public function testOrGroup()
	{
		$query = $this->db->select('id, key as k, val')
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

	public function testOrNotGroup()
	{
		$query = $this->db->select('id, key as k, val')
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
	// ! Where In tests
	// --------------------------------------------------------------------------

	public function testWhereIn()
	{
		$query = $this->db->from('test')
			->where_in('id', array(0, 6, 56, 563, 341))
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testOrWhereIn()
	{
		$query = $this->db->from('test')
			->where('key', 'false')
			->or_where_in('id', array(0, 6, 56, 563, 341))
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testWhereNotIn()
	{
		$query = $this->db->from('test')
			->where('key', 'false')
			->where_not_in('id', array(0, 6, 56, 563, 341))
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testOrWhereNotIn()
	{
		$query = $this->db->from('test')
			->where('key', 'false')
			->or_where_not_in('id', array(0, 6, 56, 563, 341))
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testSelectAvg()
	{
		$query = $this->db->select_avg('id', 'di')
			->get('test');

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testSelectSum()
	{
		$query = $this->db->select_sum('id', 'di')
			->get('test');

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testSelectDistinct()
	{
		$query = $this->db->select_sum('id', 'di')
			->distinct()
			->get('test');

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testSelectGet()
	{
		$query = $this->db->select('id, key as k, val')
			->get('test', 2, 1);

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testSelectFromGet()
	{
		$query = $this->db->select('id, key as k, val')
			->from('test ct')
			->where('id >', 1)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testSelectFromLimitGet()
	{
		$query = $this->db->select('id, key as k, val')
			->from('test ct')
			->where('id >', 1)
			->limit(3)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------
	// ! Query modifier tests
	// --------------------------------------------------------------------------

	public function testOrderBy()
	{
		$query = $this->db->select('id, key as k, val')
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
		$query = $this->db->select('id, key as k, val')
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
		$query = $this->db->select('id, key as k, val')
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
		$query = $this->db->select('id, key as k, val')
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
		$query = $this->db->from('test')
			->like('key', 'og')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testOrLike()
	{
		$query = $this->db->from('test')
			->like('key', 'og')
			->or_like('key', 'val')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testOrNotLike()
	{
		$query = $this->db->from('test')
			->like('key', 'og', 'before')
			->or_not_like('key', 'val')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testNotLike()
	{
		$query = $this->db->from('test')
			->like('key', 'og', 'before')
			->not_like('key', 'val')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testLikeBefore()
	{
		$query = $this->db->from('test')
			->like('key', 'og', 'before')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testLikeAfter()
	{
		$query = $this->db->from('test')
			->like('key', 'og', 'after')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testJoin()
	{
		$query = $this->db->from('test ct')
			->join('join cj', 'cj.id = ct.id')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testLeftJoin()
	{
		$query = $this->db->from('test ct')
			->join('join cj', 'cj.id = ct.id', 'left')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testInnerJoin()
	{
		$query = $this->db->from('test ct')
			->join('join cj', 'cj.id = ct.id', 'inner')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------
	// ! DB update tests
	// --------------------------------------------------------------------------

	public function testInsert()
	{
		$query = $this->db->set('id', 98)
			->set('key', 84)
			->set('val', 120)
			->insert('test');

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testInsertArray()
	{
		$query = $this->db->insert('test', array(
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
				'val' => 57,
			),
			array(
				'id' => 48,
				'key' => 403,
				'val' => 97,
			),
		);

		$query = $this->db->insert_batch('test', $data);

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testUpdate()
	{
		$query = $this->db->where('id', 7)
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

		$query = $this->db->set($array)
			->where('id', 22)
			->update('test');

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testWhereSetUpdate()
	{
		$query = $this->db->where('id', 36)
			->set('id', 36)
			->set('key', 'gogle')
			->set('val', 'non-word')
			->update('test');

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function testDelete()
	{
		$query = $this->db->delete('test', array('id' => 5));

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------
	// ! Non-data read queries
	// --------------------------------------------------------------------------

	public function testCountAll()
	{
		$query = $this->db->count_all('test');

		$this->assertTrue(is_numeric($query));
	}

	// --------------------------------------------------------------------------

	public function testCountAllResults()
	{
		$query = $this->db->count_all_results('test');

		$this->assertTrue(is_numeric($query));
	}

	// --------------------------------------------------------------------------

	public function testCountAllResults2()
	{
		$query = $this->db->select('id, key as k, val')
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
		$query = $this->db->get('test');

		$this->assertTrue(is_numeric($this->db->num_rows()));
	}

	// --------------------------------------------------------------------------
	// ! Compiled Query tests
	// --------------------------------------------------------------------------

	public function testGetCompiledSelect()
	{
		$sql = $this->db->get_compiled_select('test');
		$qb_res = $this->db->get('test');
		$sql_res = $this->db->query($sql);

		$this->assertEquals($qb_res, $sql_res);
	}

	public function testGetCompiledUpdate()
	{
		$sql = $this->db->set(array(
			'id' => 4,
			'key' => 'foo',
			'val' => 'baz'
		))->get_compiled_update('test');

		$this->assertTrue(is_string($sql));
	}

	public function testGetCompiledInsert()
	{
		$sql = $this->db->set(array(
			'id' => 4,
			'key' => 'foo',
			'val' => 'baz'
		))->get_compiled_insert('test');

		$this->assertTrue(is_string($sql));
	}

	public function testGetCompiledDelete()
	{
		$sql = $this->db->where('id', 4)
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
			$this->db = Query($params);
		}
		catch(BadDBDriverException $e)
		{
			$this->assertInstanceOf('BadDBDriverException', $e);
		}
	}

	// --------------------------------------------------------------------------

	/*public function testBadConnection()
	{
		$params = array(
			'host' => '127.0.0.1',
			'port' => '987896',
			'database' => 'test',
			'user' => NULL,
			'pass' => NULL,
			'type' => 'sqlite',
			'name' => 'foobar'
		);

		try
		{
			$this->db = Query($params);
		}
		catch(BadConnectionException $e)
		{
			$this->assertInstanceOf('BadConnectionException', $e);
		}
	}*/

	// --------------------------------------------------------------------------

	public function testBadMethod()
	{
		try
		{
			$res = $this->db->foo();
		}
		catch(BadMethodCallException $e)
		{
			$this->assertInstanceOf('BadMethodCallException', $e);
		}
	}

	// --------------------------------------------------------------------------

	public function testBadNumRows()
	{
		$this->db->set(array(
			'id' => 999,
			'key' => 'ring',
			'val' => 'sale'
		))->insert('test');

		$res = $this->db->num_rows();
		$this->assertEqual(NULL, $res);
	}
}

// End of db_qb_test.php