<?php
/**
 * Query
 *
 * Free Query Builder / Database Abstraction Layer
 *
 * @package		Query
 * @author		Timothy J. Warren
 * @copyright	Copyright (c) 2012 - 2013
 * @link 		https://github.com/aviat4ion/Query
 * @license		http://philsturgeon.co.uk/code/dbad-license
 */

// --------------------------------------------------------------------------

/**
 * Query builder parent test class
 */
abstract class QBTest extends UnitTestCase {

	public function __destruct()
	{
		if (isset($_GET['show_queries']))
		{
			echo '<pre>' . print_r($this->db->queries, TRUE) . '</pre>';
		}
	}

	// --------------------------------------------------------------------------
	// ! Get Tests
	// --------------------------------------------------------------------------
	
	public function TestInvalidConnectionName()
	{
		if (empty($this->db)) return;
	
		try 
		{
			$db = Query('foo');
		}
		catch (InvalidArgumentException $e)
		{
			$this->assertTrue(TRUE);
		}
	}
	
	// --------------------------------------------------------------------------

	public function TestQueryFunctionAlias()
	{
		if (empty($this->db)) return;

		$db = Query();

		$this->assertReference($this->db, $db);
	}

	// --------------------------------------------------------------------------

	public function TestFunctionGet()
	{
		if (empty($this->db))  return;

		$query = $this->db->select('id, COUNT(id) as count')
			->from('test')
			->group_by('id')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function TestGet()
	{
		if (empty($this->db))  return;

		$query = $this->db->get('create_test');

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function TestPrefixGet()
	{
		if (empty($this->db))  return;

		$query = $this->db->from('test')->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function TestGetWNumRows()
	{
		if (empty($this->db))  return;

		$query = $this->db->get('create_test');
		$numrows = count($query->fetchAll(PDO::FETCH_NUM));

		$this->assertEqual($this->db->num_rows(), $numrows);
	}

	// --------------------------------------------------------------------------

	public function TestGetLimit()
	{
		if (empty($this->db))  return;

		$query = $this->db->get('create_test', 2);

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function TestGetLimitSkip()
	{
		if (empty($this->db))  return;

		$query = $this->db->get('create_test', 2, 1);

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function TestGetWhere()
	{
		if (empty($this->db))  return;

		$query = $this->db->get_where('create_test', array('id !=' => 1), 2, 1);

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function TestHaving()
	{
		if (empty($this->db))  return;

		$query = $this->db->select('id')
			->from('create_test')
			->group_by('id')
			->having(array('id >' => 1))
			->having('id !=', 3)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function TestOrHaving()
	{
		if (empty($this->db))  return;

		$query = $this->db->select('id')
			->from('create_test')
			->group_by('id')
			->having(array('id >' => 1))
			->or_having('id !=', 3)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function TestGetViews()
	{
		if (empty($this->db))  return;

		$this->assertTrue(is_array($this->db->get_views()));
	}

	// --------------------------------------------------------------------------
	// ! Select Tests
	// --------------------------------------------------------------------------

	public function TestSelectWhereGet()
	{
		if (empty($this->db))  return;

		$query = $this->db->select('id, key as k, val')
			->where('id >', 1)
			->where('id <', 900)
			->get('create_test', 2, 1);

		$this->assertIsA($query, 'PDOStatement');
	}
	
	

	// --------------------------------------------------------------------------

	public function TestSelectWhereGet2()
	{
		if (empty($this->db))  return;

		$query = $this->db->select('id, key as k, val')
			->where('id !=', 1)
			->get('create_test', 2, 1);

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function TestSelectMax()
	{
		if (empty($this->db))  return;

		$query = $this->db->select_max('id', 'di')
			->get('test');

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function TestSelectMin()
	{
		if (empty($this->db))  return;

		$query = $this->db->select_min('id', 'di')
			->get('create_test');

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function TestMultiOrderBy()
	{
		if (empty($this->db)) return;

		$query = $this->db->from('create_test')
			->order_by('id, key')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}
	
	// --------------------------------------------------------------------------
	// ! Grouping Tests
	// --------------------------------------------------------------------------
	
	public function TestGroup()
	{
		if (empty($this->db))  return;

		$query = $this->db->select('id, key as k, val')
			->from('create_test')
			->group_start()
			->where('id >', 1)
			->where('id <', 900)
			->group_end()
			->limit(2, 1)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}
	
	public function TestOrGroup()
	{
		if (empty($this->db))  return;

		$query = $this->db->select('id, key as k, val')
			->from('create_test')
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
	
	public function TestOrNotGroup()
	{
		if (empty($this->db))  return;

		$query = $this->db->select('id, key as k, val')
			->from('create_test')
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
	// ! Where In Tests
	// --------------------------------------------------------------------------

	public function TestWhereIn()
	{
		if (empty($this->db)) return;

		$query = $this->db->from('create_test')
			->where_in('id', array(0, 6, 56, 563, 341))
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}
	
	// --------------------------------------------------------------------------

	public function TestOrWhereIn()
	{
		if (empty($this->db)) return;

		$query = $this->db->from('create_test')
			->where('key', 'false')
			->or_where_in('id', array(0, 6, 56, 563, 341))
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}
	
	// --------------------------------------------------------------------------
	
	public function TestWhereNotIn()
	{
		if (empty($this->db)) return;

		$query = $this->db->from('create_test')
			->where('key', 'false')
			->where_not_in('id', array(0, 6, 56, 563, 341))
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}
	
	// --------------------------------------------------------------------------
	
	public function TestOrWhereNotIn()
	{
		if (empty($this->db)) return;

		$query = $this->db->from('create_test')
			->where('key', 'false')
			->or_where_not_in('id', array(0, 6, 56, 563, 341))
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function TestSelectAvg()
	{
		if (empty($this->db))  return;

		$query = $this->db->select_avg('id', 'di')
			->get('create_test');

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function TestSelectSum()
	{
		if (empty($this->db))  return;

		$query = $this->db->select_sum('id', 'di')
			->get('create_test');

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function TestSelectDistinct()
	{
		if (empty($this->db))  return;

		$query = $this->db->select_sum('id', 'di')
			->distinct()
			->get('create_test');

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function TestSelectGet()
	{
		if (empty($this->db))  return;

		$query = $this->db->select('id, key as k, val')
			->get('create_test', 2, 1);

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function TestSelectFromGet()
	{
		if (empty($this->db))  return;

		$query = $this->db->select('id, key as k, val')
			->from('create_test ct')
			->where('id >', 1)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function TestSelectFromLimitGet()
	{
		if (empty($this->db))  return;

		$query = $this->db->select('id, key as k, val')
			->from('create_test ct')
			->where('id >', 1)
			->limit(3)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------
	// ! Query modifier tests
	// --------------------------------------------------------------------------

	public function TestOrderBy()
	{
		if (empty($this->db))  return;

		$query = $this->db->select('id, key as k, val')
			->from('create_test')
			->where('id >', 0)
			->where('id <', 9000)
			->order_by('id', 'DESC')
			->order_by('k', 'ASC')
			->limit(5,2)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function TestOrderByRandom()
	{
		if (empty($this->db))  return;

		$query = $this->db->select('id, key as k, val')
			->from('create_test')
			->where('id >', 0)
			->where('id <', 9000)
			->order_by('id', 'rand')
			->limit(5,2)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function TestGroupBy()
	{
		if (empty($this->db))  return;

		$query = $this->db->select('id, key as k, val')
			->from('create_test')
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
	
	//public function TestOr
	
	// --------------------------------------------------------------------------

	public function TestOrWhere()
	{
		if (empty($this->db))  return;

		$query = $this->db->select('id, key as k, val')
			->from('create_test')
			->where(' id ', 1)
			->or_where('key >', 0)
			->limit(2, 1)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function TestLike()
	{
		if (empty($this->db))  return;

		$query = $this->db->from('create_test')
			->like('key', 'og')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}
	
	// --------------------------------------------------------------------------
	
	public function TestOrLike()
	{
		if (empty($this->db))  return;
		
		$query = $this->db->from('create_test')
			->like('key', 'og')
			->or_like('key', 'val')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}
	
	// --------------------------------------------------------------------------
	
	public function TestOrNotLike()
	{
		 if (empty($this->db))  return;
		
		$query = $this->db->from('create_test')
			->like('key', 'og', 'before')
			->or_not_like('key', 'val')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}
	
	// --------------------------------------------------------------------------
	
	public function TestNotLike()
	{
		if (empty($this->db))  return;
		
		$query = $this->db->from('create_test')
			->like('key', 'og', 'before')
			->not_like('key', 'val')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}
	
	// --------------------------------------------------------------------------
	
	public function TestLikeBefore()
	{
		if (empty($this->db))  return;
		
		$query = $this->db->from('create_test')
			->like('key', 'og', 'before')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}
	
	// --------------------------------------------------------------------------
	
	public function TestLikeAfter()
	{
		if (empty($this->db))  return;
		
		$query = $this->db->from('create_test')
			->like('key', 'og', 'after')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function TestJoin()
	{
		if (empty($this->db))  return;

		$query = $this->db->from('create_test ct')
			->join('join cj', 'cj.id = ct.id')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function TestLeftJoin()
	{
		if (empty($this->db))  return;

		$query = $this->db->from('create_test ct')
			->join('join cj', 'cj.id = ct.id', 'left')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function TestInnerJoin()
	{
		if (empty($this->db))  return;

		$query = $this->db->from('create_test ct')
			->join('join cj', 'cj.id = ct.id', 'inner')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------
	// ! DB update tests
	// --------------------------------------------------------------------------

	public function TestInsert()
	{
		if (empty($this->db))  return;

		$query = $this->db->set('id', 4)
			->set('key', 4)
			->set('val', 5)
			->insert('test');

		$this->assertIsA($query, 'PDOStatement');
	}
	
	// --------------------------------------------------------------------------
	
	public function TestInsertArray()
	{
		if (empty($this->db))  return;

		$query = $this->db->insert('test', array(
				'id' => 587,
				'key' => 1,
				'val' => 2,
			));

		$this->assertIsA($query, 'PDOStatement');
	}
	
	// --------------------------------------------------------------------------

	public function TestInsertBatch()
	{
		if (empty($this->db))  return;
		
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

	public function TestUpdate()
	{
		if (empty($this->db))  return;

		$query = $this->db->where('id', 4)
			->update('create_test', array(
				'id' => 4,
				'key' => 'gogle',
				'val' => 'non-word'
			));

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function TestSetArrayUpdate()
	{
		if (empty($this->db))  return;

		$array = array(
			'id' => 4,
			'key' => 'gogle',
			'val' => 'non-word'
		);

		$query = $this->db->set($array)
			->where('id', 4)
			->update('create_test');

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function TestWhereSetUpdate()
	{
		if (empty($this->db))  return;

		$query = $this->db->where('id', 4)
			->set('id', 4)
			->set('key', 'gogle')
			->set('val', 'non-word')
			->update('test');

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function TestDelete()
	{
		if (empty($this->db))  return;

		$query = $this->db->delete('create_test', array('id' => 4));

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------
	// ! Non-data read queries
	// --------------------------------------------------------------------------

	public function TestCountAll()
	{
		if (empty($this->db))  return;
		$query = $this->db->count_all('test');

		$this->assertTrue(is_numeric($query));
	}

	// --------------------------------------------------------------------------

	public function TestCountAllResults()
	{
		if (empty($this->db))  return;
		$query = $this->db->count_all_results('test');

		$this->assertTrue(is_numeric($query));
	}

	// --------------------------------------------------------------------------

	public function TestCountAllResults2()
	{
		if (empty($this->db))  return;

		$query = $this->db->select('id, key as k, val')
			->from('test')
			->where(' id ', 1)
			->or_where('key >', 0)
			->limit(2, 1)
			->count_all_results();

		$this->assertTrue(is_numeric($query));
	}

	// --------------------------------------------------------------------------

	public function TestNumRows()
	{
	    if (empty($this->db))  return;
		
		$query = $this->db->get('create_test');

		$this->assertTrue(is_numeric($this->db->num_rows()));
	}
	
	// --------------------------------------------------------------------------
	// ! Compiled Query Tests
	// --------------------------------------------------------------------------
	
	public function TestGetCompiledSelect()
	{
		if (empty($this->db))  return;
		
		$sql = $this->db->get_compiled_select('create_test');
		$qb_res = $this->db->get('create_test');
		$sql_res = $this->db->query($sql);
		
		$this->assertClone($qb_res, $sql_res);
	}
	
	public function TestGetCompiledUpdate()
	{
		if (empty($this->db))  return;
		
		$sql = $this->db->set(array(
			'id' => 4,
			'key' => 'foo',
			'val' => 'baz'
		))->get_compiled_update('create_test');
		
		$this->assertTrue(is_string($sql));
	}
	
	public function TestGetCompiledInsert()
	{
		if (empty($this->db))  return;
		
		$sql = $this->db->set(array(
			'id' => 4,
			'key' => 'foo',
			'val' => 'baz'
		))->get_compiled_insert('create_test');
		
		$this->assertTrue(is_string($sql));
	}
	
	public function TestGetCompiledDelete()
	{
		if (empty($this->db))  return;
		
		$sql = $this->db->where('id', 4)
			->get_compiled_delete('create_test');
			
		$this->assertTrue(is_string($sql));
	}

	// --------------------------------------------------------------------------
	// ! Error Tests
	// --------------------------------------------------------------------------

	/**
	 * Handles invalid drivers
	 */
	public function TestBadDriver()
	{
		$params = array(
			'host' => '127.0.0.1',
			'port' => '3306',
			'database' => 'test',
			'user' => 'root',
			'pass' => NULL,
			'type' => 'QGYFHGEG'
		);

		$this->expectException('BadDBDriverException');

		$this->db = Query($params);
	}

	// --------------------------------------------------------------------------

	public function TestBadConnection()
	{
		$params = array(
			'host' => '127.0.0.1',
			'port' => '987896',
			'database' => 'test',
			'user' => NULL,
			'pass' => NULL,
			'type' => 'mysql',
			'name' => 'foobar'
		);

		$this->expectException('BadConnectionException');

		$this->db = @Query($params);

	}
	
	// --------------------------------------------------------------------------
	
	public function TestBadMethod()
	{
		$res = $this->db->foo();
		$this->assertEqual(NULL, $res);
	}
	
	// --------------------------------------------------------------------------
	
	public function TestBadNumRows()
	{
		$this->db->set(array(
			'id' => 999,
			'key' => 'ring',
			'val' => 'sale'
		))->insert('create_test');
		
		$res = $this->db->num_rows();
		$this->assertEqual(NULL, $res);
	}
}

// End of db_qb_test.php