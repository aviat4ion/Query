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
 * Class for testing Query Builder with SQLite 
 */
 class SQLiteQBTest extends QBTest {
 
 	function __construct()
 	{
 		parent::__construct();
 	
 		$path = TEST_DIR.DS.'test_dbs'.DS.'test_sqlite.db';
		$params = new Stdclass();
		$params->type = 'sqlite';
		$params->file = $path;
		$params->host = 'localhost';
		$this->db = new Query_Builder($params);
		
		echo '<hr /> SQLite Queries <hr />';
 	}
}