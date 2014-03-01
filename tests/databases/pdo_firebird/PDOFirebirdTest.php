<?php
/**
 * Query
 *
 * Free Query Builder / Database Abstraction Layer
 *
 * @package		Query
 * @author		Timothy J. Warren
 * @copyright	Copyright (c) 2012 - 2014
 * @link 		https://github.com/aviat4ion/Query
 * @license		http://philsturgeon.co.uk/code/dbad-license
 */

// --------------------------------------------------------------------------

require_once "../firebird/FirebirdTest.php";

/**
 * Firebirdtest class.
 *
 * @extends DBtest
 * @requires extension interbase
 */
class PDOFirebirdTest extends FirebirdTest {

	public function setUp()
	{
		$dbpath = QTEST_DIR.QDS.'db_files'.QDS.'FB_TEST_DB.FDB';

		// If the database isn't installed, skip the tests
		if ( ! class_exists("PDO_Firebird"))
		{
			$this->markTestSkipped("Firebird extension for PDO not loaded");
		}

		// test the db driver directly
		$this->db = new PDO_Firebird('localhost:'.$dbpath);
		$this->tables = $this->db->get_tables();
	}
}