<?php
/**
 * OpenSQLManager
 *
 * Free Database manager for Open Source Databases
 *
 * @author 		Timothy J. Warren
 * @copyright	Copyright (c) 2012
 * @link 		https://github.com/aviat4ion/OpenSQLManager
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
 	
 		$path = TEST_DIR.DS.'db_files'.DS.'test_sqlite.db';
		$params = new Stdclass();
		$params->type = 'sqlite';
		$params->file = $path;
		$params->host = 'localhost';
		$this->db = new Query_Builder($params);
		
		// echo '<hr /> SQLite Queries <hr />';
 	}
}