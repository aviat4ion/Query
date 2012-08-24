<?php
/**
 * Query
 *
 * Free Query Builder / Database Abstraction Layer
 *
 * @package		Query
 * @author		Timothy J. Warren
 * @copyright	Copyright (c) 2012
 * @link 		https://github.com/aviat4ion/Query
 * @license		http://philsturgeon.co.uk/code/dbad-license
 */

// --------------------------------------------------------------------------

/**
 * Query builder parent test class
 */
abstract class QBTest extends UnitTestCase {

	// --------------------------------------------------------------------------
	// ! Get Tests
	// --------------------------------------------------------------------------

	public function TestGet()
	{
		if (empty($this->db))  return;

		$query = $this->db->get('create_test');

		$this->assertIsA($query, 'PDOStatement');
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
			->get('create_test');

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

	public function TestWhereIn()
	{
		if (empty($this->db)) return;

		$query = $this->db->from('create_test')
			->where_in('id', array(0, 6, 56, 563, 341))
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
			->group_by('id')
			->group_by('val')
			->order_by('id', 'DESC')
			->order_by('k', 'ASC')
			->limit(5,2)
			->get();

		$this->assertIsA($query, 'PDOStatement');
	}

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

	public function TestJoin()
	{
		if (empty($this->db))  return;

		$query = $this->db->from('create_test')
			->join('create_join cj', 'cj.id = create_test.id')
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
			->insert('create_test');

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function TestUpdate()
	{
		if (empty($this->db))  return;

		$query = $this->db->set('id', 4)
			->set('key', 'gogle')
			->set('val', 'non-word')
			->where('id', 4)
			->update('create_test');

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------

	public function TestDelete()
	{
		if (empty($this->db))  return;

		$query = $this->db->where('id', 4)->delete('create_test');

		$this->assertIsA($query, 'PDOStatement');
	}

	// --------------------------------------------------------------------------
	// ! Non-data read queries
	// --------------------------------------------------------------------------

	public function TestCountAll()
	{
		if (empty($this->db))  return;
		$query = $this->db->count_all('create_test');

		$this->assertTrue(is_numeric($query));
	}

	// --------------------------------------------------------------------------

	public function TestCountAllResults()
	{
		if (empty($this->db))  return;
		$query = $this->db->count_all_results('create_test');

		$this->assertTrue(is_numeric($query));
	}

	// --------------------------------------------------------------------------

	public function TestCountAllResults2()
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

	// --------------------------------------------------------------------------

	public function TestNumRows()
	{
		$query = $this->db->get('create_test');

		$this->assertTrue(is_numeric($this->db->num_rows()));
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

		$this->db = new Query_Builder($params);
	}

	// --------------------------------------------------------------------------

	public function TestBadConnection()
	{
		$params = array(
			'host' => '127.0.0.1',
			'port' => '3306',
			'database' => 'test',
			'user' => NULL,
			'pass' => NULL,
			'type' => 'pgsql'
		);

		$this->expectException('BadConnectionException');

		$this->db = new Query_Builder($params);

	}
}

// End of db_qb_test.php