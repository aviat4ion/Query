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

		$dbpath = QTEST_DIR.QDS.'db_files'.QDS.'FB_TEST_DB.FDB';

		// Test the query builder
		$params = new Stdclass();
		$params->type = 'firebird';
		$params->file = $dbpath;
		$params->host = 'localhost';
		$params->user = 'sysdba';
		$params->pass = 'masterkey';
		$params->prefix = 'create_';
		$this->db = Query($params);

		// echo '<hr /> Firebird Queries <hr />';
	}

	public function TestTypeList()
	{
		$sql = $this->db->sql->type_list();
		$query = $this->db->query($sql);

		$this->assertIsA($query, 'PDOStatement');

		$res = $query->fetchAll(PDO::FETCH_ASSOC);

		$this->assertTrue(is_array($res));
	}
}