<?php
/**
 * Query
 *
 * Free Query Builder / Database Abstraction Layer
 *
 * @author 		Timothy J. Warren
 * @copyright	Copyright (c) 2012
 * @link 		https://github.com/aviat4ion/Query
 * @license 	http://philsturgeon.co.uk/code/dbad-license 
 */

// --------------------------------------------------------------------------

/**
 * Query builder parent test class
 */
abstract class QBTest extends UnitTestCase {

	// ! Get Tests

	function TestGet()
	{
		if (empty($this->db))  return;

		$query = $this->db->get('create_test');

		$this->assertIsA($query, 'PDOStatement');
	}

	function TestGetLimit()
	{
		if (empty($this->db))  return;

		$query = $this->db->get('create_test', 2);

		$this->assertIsA($query, 'PDOStatement');
	}

	function TestGetLimitSkip()
	{
		if (empty($this->db))  return;

		$query = $this->db->get('create_test', 2, 1);

		$this->assertIsA($query, 'PDOStatement');
	}
	
	function TestGetWhere()
	{
		if (empty($this->db))  return;
		
		$query = $this->db->get_where('create_test', array('id !=' => 1), 2, 1);
		
		$this->assertIsA($query, 'PDOStatement');
	}
	
	function TestHaving()
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
	
	function TestOrHaving()
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
	
	function TestGetViews()
	{
		if (empty($this->db))  return;
	
		$this->assertTrue(is_array($this->db->get_views()));
	}
	
	// ! Select Tests

	function TestSelectWhereGet()
	{
		if (empty($this->db))  return;

		$query = $this->db->select('id, key as k, val')
			->where('id >', 1)
			->where('id <', 900)
			->get('create_test', 2, 1);

		$this->assertIsA($query, 'PDOStatement');
	}

	function TestSelectWhereGet2()
	{
		if (empty($this->db))  return;

		$query = $this->db->select('id, key as k, val')
			->where('id !=', 1)
			->get('create_test', 2, 1);

		$this->assertIsA($query, 'PDOStatement');
	}
	
	function TestSelectMax()
	{
		if (empty($this->db))  return;
	
		$query = $this->db->select_max('id', 'di')
			->get('create_test');
			
		$this->assertIsA($query, 'PDOStatement');
	}
	
	function TestSelectMin()
	{
		if (empty($this->db))  return;
	
		$query = $this->db->select_min('id', 'di')
			->get('create_test');
			
		$this->assertIsA($query, 'PDOStatement');
	}
	
	function TestSelectAvg()
	{
		if (empty($this->db))  return;
	
		$query = $this->db->select_avg('id', 'di')
			->get('create_test');
			
		$this->assertIsA($query, 'PDOStatement');
	}
	
	function TestSelectSum()
	{
		if (empty($this->db))  return;
	
		$query = $this->db->select_sum('id', 'di')
			->get('create_test');
			
		$this->assertIsA($query, 'PDOStatement');
	}
	
	function TestSelectDistinct()
	{
		if (empty($this->db))  return;
	
		$query = $this->db->select_sum('id', 'di')
			->distinct()
			->get('create_test');
			
		$this->assertIsA($query, 'PDOStatement');
	}

	function TestSelectGet()
	{
		if (empty($this->db))  return;

		$query = $this->db->select('id, key as k, val')
			->get('create_test', 2, 1);

		$this->assertIsA($query, 'PDOStatement');
	}

	function TestSelectFromGet()
	{
		if (empty($this->db))  return;

		$query = $this->db->select('id, key as k, val')
			->from('create_test ct')
			->where('id >', 1)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	function TestSelectFromLimitGet()
	{
		if (empty($this->db))  return;

		$query = $this->db->select('id, key as k, val')
			->from('create_test ct')
			->where('id >', 1)
			->limit(3)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}
	
	// ! Query modifier tests

	function TestOrderBy()
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

	function TestOrderByRandom()
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

	function TestGroupBy()
	{
		if (empty($this->db))  return;

		$query = $this->db->select('id, key as k, val')
			->from('create_test')
			->where('id >', 0)
			->where('id <', 9000)
			->group_by('k')
			->group_by('id')
			->group_by('val')
			->order_by('id', 'DESC')
			->order_by('k', 'ASC')
			->limit(5,2)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	function TestOrWhere()
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

	function TestLike()
	{
		if (empty($this->db))  return;

		$query = $this->db->from('create_test')
			->like('key', 'og')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

	function TestJoin()
	{
		if (empty($this->db))  return;

		$query = $this->db->from('create_test')
			->join('create_join cj', 'cj.id = create_test.id')
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}
	
	// ! DB update tests

	function TestInsert()
	{
		if (empty($this->db))  return;

		$query = $this->db->set('id', 4)
			->set('key', 4)
			->set('val', 5)
			->insert('create_test');

		$this->assertIsA($query, 'PDOStatement');
	}

	function TestUpdate()
	{
		if (empty($this->db))  return;

		$query = $this->db->set('id', 4)
			->set('key', 'gogle')
			->set('val', 'non-word')
			->where('id', 4)
			->update('create_test');

		$this->assertIsA($query, 'PDOStatement');
	}

	function TestDelete()
	{
		if (empty($this->db))  return;

		$query = $this->db->where('id', 4)->delete('create_test');

		$this->assertIsA($query, 'PDOStatement');
	}
	
	// ! Non-data read queries
	
	function TestCountAll()
	{
		if (empty($this->db))  return;
		$query = $this->db->count_all('create_test');
		
		$this->assertTrue(is_numeric($query));
	}
	
	function TestCountAllResults()
	{
		if (empty($this->db))  return;
		$query = $this->db->count_all_results('create_test');
		
		$this->assertTrue(is_numeric($query));
	}
	
	function TestCountAllResults2()
	{
		if (empty($this->db))  return;
	
		$query = $this->db->select('id, key as k, val')
			->from('create_test')
			->where(' id ', 1)
			->or_where('key >', 0)
			->limit(2, 1)
			->count_all_results();
			
		$this->assertTrue(is_numeric($query));
	}
	
}

// End of db_qb_test.php