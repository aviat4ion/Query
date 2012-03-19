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
 * Parent Database Test Class
 */
abstract class DBTest extends UnitTestCase {

	abstract function TestConnection();

	function tearDown()
	{
		unset($this->db);
	}
	
	function TestGetTables()
	{
		$tables = $this->db->get_tables();
		$this->assertTrue(is_array($tables));
	}
	
	function TestGetSystemTables()
	{
		$tables = $this->db->get_system_tables();
		
		$this->assertTrue(is_array($tables));
	}
	
	function TestCreateTransaction()
	{
		$res = $this->db->beginTransaction();
		$this->assertTrue($res);
	}
	
	function TestPreparedStatements()
	{
		$sql = <<<SQL
			INSERT INTO "create_test" ("id", "key", "val") 
			VALUES (?,?,?)
SQL;
		$statement = $this->db->prepare_query($sql, array(1,"boogers", "Gross"));
		
		$statement->execute();

	}
	
	function TestPrepareExecute()
	{
		$sql = <<<SQL
			INSERT INTO "create_test" ("id", "key", "val") 
			VALUES (?,?,?)
SQL;
		$this->db->prepare_execute($sql, array(
			2, "works", 'also?'
		));
	
	}
	
	function TestCommitTransaction()
	{
		$res = $this->db->beginTransaction();
		
		$sql = 'INSERT INTO "create_test" ("id", "key", "val") VALUES (10, 12, 14)';
		$this->db->query($sql);
	
		$res = $this->db->commit();
		$this->assertTrue($res);
	}
	
	function TestRollbackTransaction()
	{
		$res = $this->db->beginTransaction();
		
		$sql = 'INSERT INTO "create_test" ("id", "key", "val") VALUES (182, 96, 43)';
		$this->db->query($sql);
	
		$res = $this->db->rollback();
		$this->assertTrue($res);
	}
}

// --------------------------------------------------------------------------

/**
 * Query builder parent test class
 */
abstract class QBTest extends UnitTestCase {

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

}

