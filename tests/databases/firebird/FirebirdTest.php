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

@chmod(QTEST_DIR.QDS.'db_files'.QDS.'FB_TEST_DB.FDB', 0777);

/**
 * Firebirdtest class.
 *
 * @extends DBtest
 * @requires extension interbase
 */
class FirebirdTest extends DBtest {

	public static function setupBeforeClass()
	{
		$dbpath = QTEST_DIR.QDS.'db_files'.QDS.'FB_TEST_DB.FDB';

		// test the db driver directly
		self::$db = new \Query\Drivers\Firebird\Driver('localhost:'.$dbpath);
		self::$db->set_table_prefix('create_');
	}

	public function setUp()
	{
		if ( ! function_exists('\\fbird_connect'))
		{
			$this->markTestSkipped('Firebird extension does not exist');
		}

		$this->tables = self::$db->get_tables();
	}

	// --------------------------------------------------------------------------

	public function tearDown()
	{
		unset($this->tables);
	}

	// --------------------------------------------------------------------------

	/**
	 * coverage for methods in result class that aren't implemented
	 */
	public function testNullResultMethods()
	{
		$obj = self::$db->query('SELECT "id" FROM "create_test"');

		$val = "bar";

		$this->assertNull($obj->bindColumn('foo', $val));
		$this->assertNull($obj->bindParam('foo', $val));
		$this->assertNull($obj->bindValue('foo', $val));

	}

	// --------------------------------------------------------------------------

	public function testExists()
	{
		$this->assertTrue(function_exists('ibase_connect'));
		$this->assertTrue(function_exists('fbird_connect'));
	}

	// --------------------------------------------------------------------------

	public function testConnection()
	{
		$this->assertIsA(self::$db, '\\Query\\Drivers\\Firebird\\Driver');
	}

	// --------------------------------------------------------------------------

	public function testGetSystemTables()
	{
		$only_system = TRUE;

		$tables = self::$db->get_system_tables();

		foreach($tables as $t)
		{
			if(stripos($t, 'rdb$') !== 0 && stripos($t, 'mon$') !== 0)
			{
				$only_system = FALSE;
				break;
			}
		}

		$this->assertTrue($only_system);
	}

	// --------------------------------------------------------------------------
	// ! Create / Delete Tables
	// --------------------------------------------------------------------------

	public function testCreateTable()
	{
		//Attempt to create the table
		$sql = self::$db->get_util()->create_table('create_delete', array(
			'id' => 'SMALLINT',
			'key' => 'VARCHAR(64)',
			'val' => 'BLOB SUB_TYPE TEXT'
		));
		self::$db->query($sql);

		//Check
		$this->assertTrue(in_array('create_delete', self::$db->get_tables()));
	}

	// --------------------------------------------------------------------------

	public function testDeleteTable()
	{
		//Attempt to delete the table
		$sql = self::$db->get_util()->delete_table('create_delete');
		self::$db->query($sql);

		//Check
		$table_exists = in_array('create_delete', self::$db->get_tables());
		$this->assertFalse($table_exists);
	}

	// --------------------------------------------------------------------------

	public function testTruncate()
	{
		self::$db->truncate('create_test');

		$this->assertTrue(self::$db->affected_rows() > 0);
	}

	// --------------------------------------------------------------------------

	public function testCommitTransaction()
	{
		$res = self::$db->beginTransaction();

		$sql = 'INSERT INTO "create_test" ("id", "key", "val") VALUES (10, 12, 14)';
		self::$db->query($sql);

		$res = self::$db->commit();
		$this->assertTrue($res);
	}

	// --------------------------------------------------------------------------

	public function testRollbackTransaction()
	{
		$res = self::$db->beginTransaction();

		$sql = 'INSERT INTO "create_test" ("id", "key", "val") VALUES (182, 96, 43)';
		self::$db->query($sql);

		$res = self::$db->rollback();
		$this->assertTrue($res);
	}

	// --------------------------------------------------------------------------

	public function testPreparedStatements()
	{
		$sql = <<<SQL
			INSERT INTO "create_test" ("id", "key", "val")
			VALUES (?,?,?)
SQL;
		$query = self::$db->prepare($sql);
		$query->execute(array(1,"booger's", "Gross"));

	}

	// --------------------------------------------------------------------------

	public function testPrepareExecute()
	{
		$sql = <<<SQL
			INSERT INTO "create_test" ("id", "key", "val")
			VALUES (?,?,?)
SQL;
		self::$db->prepare_execute($sql, array(
			2, "works", 'also?'
		));

	}

	// --------------------------------------------------------------------------

	public function testFetch()
	{
		$res = self::$db->query('SELECT "key","val" FROM "create_test"');

		// Object
		$fetchObj = $res->fetchObject();
		$this->assertIsA($fetchObj, 'stdClass');

		// Associative array
		$fetchAssoc = $res->fetch(PDO::FETCH_ASSOC);
		$this->assertTrue(array_key_exists('key', $fetchAssoc));

		// Numeric array
		$res2 = self::$db->query('SELECT "id","key","val" FROM "create_test"');
		$fetch = $res2->fetch(PDO::FETCH_NUM);
		$this->assertTrue(is_array($fetch));
	}

	// --------------------------------------------------------------------------

	public function testPrepareQuery()
	{
		$this->assertNull(self::$db->prepare_query('', array()));
	}

	// --------------------------------------------------------------------------

	public function testErrorInfo()
	{
		$result = self::$db->errorInfo();

		$expected = array (
		  0 => 0,
		  1 => false,
		  2 => false,
		);

		$this->assertEqual($expected, $result);
	}

	// --------------------------------------------------------------------------

	public function testErrorCode()
	{
		$result = self::$db->errorCode();
		$this->assertFalse($result);
	}

	// --------------------------------------------------------------------------

	public function testDBList()
	{
		$res = self::$db->get_sql()->db_list();
		$this->assertNULL($res);
	}

	// --------------------------------------------------------------------------

	public function testExec()
	{
		$res = self::$db->exec('SELECT * FROM "create_test"');
		$this->assertEquals(NULL, $res);
	}

	// --------------------------------------------------------------------------

	public function testInTransaction()
	{
		self::$db->beginTransaction();
		$this->assertTrue(self::$db->inTransaction());
		self::$db->rollBack();
		$this->assertFalse(self::$db->inTransaction());
	}

	// --------------------------------------------------------------------------

	public function testGetAttribute()
	{
		$res = self::$db->getAttribute("foo");
		$this->assertEquals(NULL, $res);
	}

	// --------------------------------------------------------------------------

	public function testSetAttribute()
	{
		$this->assertFalse(self::$db->setAttribute(47, 'foo'));
	}

	public function testLastInsertId()
	{
		$this->assertEqual(0, self::$db->lastInsertId('NEWTABLE_SEQ'));
	}
}