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
 * Firebird Query Builder Tests
 */
class FirebirdQBTest extends QBTest {

	public function __construct()
	{
		parent::__construct();

		$dbpath = QTEST_DIR.QDS.'db_files'.QDS.'FB_TEST_DB.FDB';

		// Test the query builder
		$params = new Stdclass();
		$params->name = 'fire';
		$params->type = 'firebird';
		$params->file = $dbpath;
		$params->host = 'localhost';
		$params->user = 'sysdba';
		$params->pass = 'masterkey';
		$params->prefix = 'create_';
		$this->db = Query($params);

		// echo '<hr /> Firebird Queries <hr />';
	}
	
	// --------------------------------------------------------------------------
	
	public function TestGetCompiledSelect()
	{
		$sql = $this->db->get_compiled_select('create_test');
		$qb_res = $this->db->get('create_test');
		$sql_res = $this->db->query($sql);
		
		$this->assertIsA($qb_res, 'Firebird_Result');
		$this->assertIsA($sql_res, 'Firebird_Result');
	}

	public function TestInsertBatch()
	{
		if (empty($this->db))  return;
		
		$insert_array = array(
			array(
				'id' => 6,
				'key' => 2,
				'val' => 3
			),
			array(
				'id' => 5,
				'key' => 6,
				'val' => 7
			),
			array(
				'id' => 8,
				'key' => 1,
				'val' => 2
			)
		);

		$query = $this->db->insert_batch('test', $insert_array);

		$this->assertNull($query);
	}
	
	// --------------------------------------------------------------------------

	public function TestTypeList()
	{
		$sql = $this->db->sql->type_list();
		$query = $this->db->query($sql);

		$this->assertIsA($query, 'PDOStatement');

		$res = $query->fetchAll(PDO::FETCH_ASSOC);

		$this->assertTrue(is_array($res));
	}
	
	// --------------------------------------------------------------------------
	
	public function testQueryExplain()
	{
		$res = $this->db->select('id, key as k, val')
			->explain()
			->where('id >', 1)
			->where('id <', 900)
			->limit(2, 1)
			->get_compiled_select('create_test');
		
		$expected = 'SELECT FIRST 2 SKIP 1 "id","key" AS "k","val" FROM "create_test" WHERE "id" > ? AND "id" < ?';
		
		$this->assertEqual($expected, $res);
	}

}