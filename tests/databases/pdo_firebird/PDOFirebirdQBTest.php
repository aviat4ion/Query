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

require_once "../firebird/FirebirdQBTest.php";

/**
 * Firebird Query Builder Tests
 * @requires extension interbase
 */
class PDOFirebirdQBTest extends FirebirdQBTest {

	public function setUp()
	{
		$dbpath = QTEST_DIR.QDS.'db_files'.QDS.'FB_TEST_DB.FDB';

		// If the database isn't installed, skip the tests
		if ( ! class_exists("PDO_Firebird"))
		{
			$this->markTestSkipped("Firebird extension for PDO not loaded");
		}

		// test the query builder
		$params = new Stdclass();
		$params->alias = 'pdo_fire';
		$params->type = 'pdo_firebird';
		$params->file = $dbpath;
		$params->host = 'localhost';
		$params->user = 'sysdba';
		$params->pass = 'masterkey';
		$params->prefix = 'create_';
		$params->options = array();
		$params->options[PDO::ATTR_PERSISTENT] = TRUE;
		$this->db = Query($params);
	}

	// --------------------------------------------------------------------------

	public function testGetNamedConnectionException()
	{
		try
		{
			$db = Query('pdo_fire');
		}
		catch(InvalidArgumentException $e)
		{
			$this->assertIsA($e, 'InvalidArgumentException');
		}
	}

	// --------------------------------------------------------------------------

	public function testGetNamedConnection()
	{
		$dbpath = QTEST_DIR.QDS.'db_files'.QDS.'FB_TEST_DB.FDB';

		// test the query builder
		$params = new Stdclass();
		$params->alias = 'pdo_fire';
		$params->type = 'pdo_firebird';
		$params->file = $dbpath;
		$params->host = 'localhost';
		$params->user = 'sysdba';
		$params->pass = 'masterkey';
		$params->prefix = '';
		$f_conn = Query($params);

		$this->assertReference($f_conn, Query('fire'));
	}
}