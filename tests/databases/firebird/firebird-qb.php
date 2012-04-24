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
 * Firebird Query Builder Tests
 */
class FirebirdQBTest extends QBTest {

	public function __construct()
	{
		parent::__construct();
		
		$dbpath = TEST_DIR.DS.'db_files'.DS.'FB_TEST_DB.FDB';

		// Test the query builder
		$params = new Stdclass();
		$params->type = 'firebird';
		$params->file = $dbpath;
		$params->host = 'localhost';
		$params->user = 'sysdba';
		$params->pass = 'masterkey';
		$this->db = new Query_Builder($params);
		
		// echo '<hr /> Firebird Queries <hr />';
	}
}