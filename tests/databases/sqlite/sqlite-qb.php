<?php
/**
 * OpenSQLManager
 *
 * Free Database manager for Open Source Databases
 *
 * @author 		Timothy J. Warren
 * @copyright	Copyright (c) 2012 - 2013
 * @link 		https://github.com/aviat4ion/OpenSQLManager
 * @license 	http://philsturgeon.co.uk/code/dbad-license
 */

// --------------------------------------------------------------------------

/**
 * Class for testing Query Builder with SQLite
 */
 class SQLiteQBTest extends QBTest {

 	public function __construct()
 	{
 		parent::__construct();

 		$path = QTEST_DIR.QDS.'db_files'.QDS.'test_sqlite.db';
		$params = new Stdclass();
		$params->type = 'sqlite';
		$params->file = $path;
		$params->host = 'localhost';
		$params->prefix = 'create_';
		$this->db = Query($params);

		// echo '<hr /> SQLite Queries <hr />';
 	}
 	
 	// --------------------------------------------------------------------------
 	
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
}