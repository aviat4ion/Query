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

/**
 * Firebird Query Builder Tests
 * @requires extension interbase
 */
class PDOFirebirdQBTest extends QBTest {

	public function setUp()
	{
		if ( ! in_array('firebird', PDO::getAvailableDrivers()))
		{
			$this->markTestSkipped('PDO Firebird extension does not exist');
		}

		$dbpath = QTEST_DIR.QDS.'db_files'.QDS.'FB_TEST_DB.FDB';

		// test the query builder
		$params = new Stdclass();
		$params->alias = 'fire';
		$params->type = 'pdo_firebird';
		$params->database = $dbpath;
		$params->host = 'localhost';
		$params->user = 'SYSDBA';
		$params->pass = 'masterkey';
		$params->prefix = 'create_';

		self::$db = Query($params);
	}

	public function testQueryFunctionAlias()
	{
$this->markTestSkipped();
		if (version_compare(PHP_VERSION, '7.0.0', '<='))
		{
			$this->markTestSkipped("Segfaults on this version of PHP");
		}

		$db = Query();

		$this->assertTrue(self::$db === $db);
	}

	public function testGetNamedConnectionException()
	{
		try
		{
			$db = Query('water');
		}
		catch(InvalidArgumentException $e)
		{
			$this->assertIsA($e, 'InvalidArgumentException');
		}
	}

	public function testGetNamedConnection()
	{
		$dbpath = QTEST_DIR.QDS.'db_files'.QDS.'FB_TEST_DB.FDB';

		// test the query builder
		$params = new Stdclass();
		$params->alias = 'wood';
		$params->type = 'pdo_firebird';
		$params->database = $dbpath;
		$params->host = 'localhost';
		$params->user = 'sysdba';
		$params->pass = 'masterkey';
		$params->prefix = '';
		$f_conn = Query($params);
		$q_conn = Query('wood');

		$this->assertReference($f_conn, $q_conn);
	}

	// --------------------------------------------------------------------------

	public function testTypeList()
	{
$this->markTestIncomplete();
		if (version_compare(PHP_VERSION, '7.0.0', '<='))
		{
			$this->markTestSkipped("Segfaults on this version of PHP");
		}

		$this->doSetUp();
		$sql = self::$db->get_sql()->type_list();
		$query = self::$db->query($sql);

		$this->assertIsA('PDOStatement', $query);

		$res = $query->fetchAll(PDO::FETCH_ASSOC);

		$this->assertTrue(is_array($res));
	}

	// --------------------------------------------------------------------------

	public function testQueryExplain()
	{
		$res = self::$db->select('id, key as k, val')
			->explain()
			->where('id >', 1)
			->where('id <', 900)
			->limit(2, 1)
			->get_compiled_select();

		$res2 = self::$db->select('id, key as k, val')
			->where('id >', 1)
			->where('id <', 900)
			->limit(2, 1)
			->get_compiled_select();

		// Queries are equal because explain is not a keyword in Firebird
		$this->assertEqual($res, $res2);
	}
}