<?php declare(strict_types=1);
/**
 * Query
 *
 * SQL Query Builder / Database Abstraction Layer
 *
 * PHP version 7
 *
 * @package     Query
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2012 - 2016 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link        https://git.timshomepage.net/aviat4ion/Query
 */


// --------------------------------------------------------------------------

/**
 * Firebird Query Builder Tests
 * @requires extension interbase
 */
class FirebirdQBTest extends QBTest {

	public static function setUpBeforeClass()
	{
		$dbpath = QTEST_DIR.QDS.'db_files'.QDS.'FB_TEST_DB.FDB';

		// test the query builder
		$params = new Stdclass();
		$params->alias = 'fire';
		$params->type = 'firebird';
		$params->file = $dbpath;
		$params->host = '127.0.0.1';
		$params->user = 'SYSDBA';
		$params->pass = 'masterkey';
		$params->prefix = 'create_';
		self::$db = Query($params);
	}

	public function setUp()
	{
		if ( ! function_exists('\\fbird_connect'))
		{
			$this->markTestSkipped('Firebird extension does not exist');
		}
	}

	// --------------------------------------------------------------------------

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

	// --------------------------------------------------------------------------

	public function testQueryFunctionAlias()
	{
		$db = Query();

		$this->assertTrue(self::$db === $db);
	}

	// --------------------------------------------------------------------------

	public function testGetNamedConnection()
	{
		$dbpath = QTEST_DIR.QDS.'db_files'.QDS.'FB_TEST_DB.FDB';

		// test the query builder
		$params = new Stdclass();
		$params->alias = 'wood';
		$params->type = 'firebird';
		$params->file = $dbpath;
		$params->host = 'localhost';
		$params->user = 'sysdba';
		$params->pass = 'masterkey';
		$params->prefix = '';
		$fConn = Query($params);
		$qConn = Query('wood');

		$this->assertReference($fConn, $qConn);
	}

	// --------------------------------------------------------------------------

	public function testTypeList()
	{
		$sql = self::$db->sql->typeList();
		$query = self::$db->query($sql);

		$this->assertIsA($query, 'PDOStatement');

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
			->getCompiledSelect();

		$res2 = self::$db->select('id, key as k, val')
			->where('id >', 1)
			->where('id <', 900)
			->limit(2, 1)
			->getCompiledSelect();

		// Queries are equal because explain is not a keyword in Firebird
		$this->assertEqual($res, $res2);
	}

	// --------------------------------------------------------------------------

	public function testResultErrors()
	{
		$obj = self::$db->query('SELECT * FROM "create_test"');

		// Test row count
		$this->assertEqual(0, $obj->rowCount());

		// Test error code
		$this->assertFalse($obj->errorCode());

		// Test error info
		$error = $obj->errorInfo();
		$expected = array (
		  0 => 0,
		  1 => false,
		  2 => false,
		);

		$this->assertEqual($expected, $error);
	}

	// --------------------------------------------------------------------------

	public function testBackupStructure()
	{
		$existing = QTEST_DIR.QDS.'db_files'.QDS.'FB_TEST_DB.FDB';
		$backup = QTEST_DIR.QDS.'db_files'.QDS.'FB_TEST_BKP.FDB';

		$this->assertTrue(self::$db->getUtil()->backupStructure($existing, $backup));
	}
}