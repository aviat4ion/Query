<?php
/**
 * OpenSQLManager
 *
 * Free Database manager for Open Source Databases
 *
 * @author 		Timothy J. Warren
 * @copyright	Copyright (c) 2012 - 2014
 * @link 		https://github.com/aviat4ion/OpenSQLManager
 * @license 	http://philsturgeon.co.uk/code/dbad-license
 */

// --------------------------------------------------------------------------

/**
 * Class for testing Query Builder with SQLite
 * 
 * @requires extension pdo_sqlite
 */
 class SQLiteQBTest extends QBTest {

 	public function setUp()
 	{
 		$path = QTEST_DIR.QDS.'db_files'.QDS.'test_sqlite.db';
		$params = (object) array(
			'type' => 'sqlite',
			'file' => $path,
			'host' => 'localhost',
			'prefix' => 'create_',
			'options' => array(
				PDO::ATTR_PERSISTENT => TRUE
			)
		);
		$this->db = Query($params);

		// echo '<hr /> SQLite Queries <hr />';
 	}
 	
 	public function testInsert() { $this->markTestSkipped();}
 	public function testInsertArray() { $this->markTestSkipped();}
 	public function testUpdate() { $this->markTestSkipped();}
 	public function testSetArrayUpdate() { $this->markTestSkipped();}
 	public function testWhereSetUpdate() { $this->markTestSkipped();}
 	public function testDelete() { $this->markTestSkipped();}
 	public function testBadNumRows() { $this->markTestSkipped();}
 	
 	// --------------------------------------------------------------------------
 	
 	public function testInsertBatch()
	{
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
	
	public function testQueryExplain()
	{
		$query = $this->db->select('id, key as k, val')
			->explain()
			->where('id >', 1)
			->where('id <', 900)
			->get('create_test', 2, 1);
			
		$res = $query->fetchAll(PDO::FETCH_ASSOC);
		
		$expected_possibilities = array();
		
		$expected_possibilities[] = array(
			array(
				'order' => '0',
				'from' => '0',
				'detail' => 'TABLE create_test USING PRIMARY KEY',
			)
		);
		
		$expected_possibilities[] = array (
			array (
				'selectid' => '0',
				'order' => '0',
				'from' => '0',
				'detail' => 'SEARCH TABLE create_test USING INTEGER PRIMARY KEY (rowid>? AND rowid<?) (~60000 rows)',
			),
		);
		
		$expected_possibilities[] = array (
			array (
				'selectid' => '0',
				'order' => '0',
				'from' => '0',
				'detail' => 'SEARCH TABLE create_test USING INTEGER PRIMARY KEY (rowid>? AND rowid<?)',
			),
		);
		
		$passed = FALSE;
		
		// Check for a matching possibility
		foreach($expected_possibilities as $ep)
		{
			if ($res == $ep)
			{
				$this->assertTrue(TRUE);
				$passed = TRUE;
			}
		}
		
		// Well, apparently not an expected possibility
		if ( ! $passed)
		{
			var_export($res);
			$this->assertTrue(FALSE);
		}
	}
}