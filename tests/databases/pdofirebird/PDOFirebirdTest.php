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

@chmod(QTEST_DIR.QDS.'db_files'.QDS.'FB_TEST_DB.FDB', 0777);

/**
 * Firebirdtest class.
 *
 * @extends DBtest
 * @requires extension interbase
 */
class PDOFirebirdTest extends DBtest {

	public static function setUpBeforeClass()
	{
		$dbpath = QTEST_DIR.QDS.'db_files'.QDS.'FB_TEST_DB.FDB';

		// test the db driver directly
		self::$db = new \Query\Drivers\Pdo_firebird\Driver('firebird:host=localhost;dbname='.$dbpath);
		self::$db->set_table_prefix('create_');
	}

	public function setUp()
	{
		if ( ! in_array('firebird', PDO::getAvailableDrivers()))
		{
			$this->markTestSkipped('PDO Firebird extension does not exist');
		}

		$this->tables = self::$db->get_tables();
	}

	// --------------------------------------------------------------------------

	public function tearDown()
	{
		unset($this->tables);
	}

	// --------------------------------------------------------------------------

	public function testExists()
	{
		$this->assertTrue(in_array('firebird', PDO::getAvailableDrivers()));;
	}

	// --------------------------------------------------------------------------

	public function testConnection()
	{
		$this->assertIsA(self::$db, '\\Query\\Drivers\\Pdo_firebird\\Driver');
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

	public function testBackupStructure()
	{
		$this->assertNull(self::$db->get_util()->backup_structure());
	}

	// --------------------------------------------------------------------------
	// ! Create / Delete Tables
	// --------------------------------------------------------------------------

	public function testCreateTable()
	{
$this->markTestSkipped();
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
$this->markTestSkipped();
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
$this->markTestSkipped();
		self::$db->truncate('create_test');

		$this->assertTrue(self::$db->affected_rows() > 0);
	}

	// --------------------------------------------------------------------------

	public function testCommitTransaction()
	{
$this->markTestSkipped();
		$res = self::$db->beginTransaction();

		$sql = 'INSERT INTO "create_test" ("id", "key", "val") VALUES (10, 12, 14)';
		self::$db->query($sql);

		$res = self::$db->commit();
		$this->assertTrue($res);
	}

	// --------------------------------------------------------------------------

	public function testRollbackTransaction()
	{
$this->markTestSkipped();
		$res = self::$db->beginTransaction();

		$sql = 'INSERT INTO "create_test" ("id", "key", "val") VALUES (182, 96, 43)';
		self::$db->query($sql);

		$res = self::$db->rollback();
		$this->assertTrue($res);
	}

	// --------------------------------------------------------------------------

	public function testPreparedStatements()
	{
$this->markTestSkipped();
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

	/*public function testFetch()
	{
		$res = self::$db->query('SELECT "key","val" FROM "create_test"');

		// Object
		$fetchObj = $res->fetchObject();
		$this->assertIsA($fetchObj, 'stdClass');

		// Associative array
		$fetchAssoc = $res->fetch(PDO::FETCH_ASSOC);
		$this->assertTrue(is_array($fetchAssoc));
		$this->assertTrue(array_key_exists('key', $fetchAssoc));

		// Numeric array
		$res2 = self::$db->query('SELECT "id","key","val" FROM "create_test"');
		$fetch = $res2->fetch(PDO::FETCH_NUM);
		$this->assertTrue(is_array($fetch));
	}*/

	// --------------------------------------------------------------------------

	/*public function testPrepareQuery()
	{
		$this->assertNull(self::$db->prepare_query('', array()));
	}*/

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
		$this->assertEquals(00000, $result);
	}

	// --------------------------------------------------------------------------

	public function testDBList()
	{
		$res = self::$db->get_sql()->db_list();
		$this->assertNULL($res);
	}

	// --------------------------------------------------------------------------

	/*public function testExec()
	{
		$res = self::$db->exec('SELECT * FROM "create_test"');
		$this->assertEquals(NULL, $res);
	}*/

	// --------------------------------------------------------------------------

	public function testInTransaction()
	{
$this->markTestSkipped();
		self::$db->beginTransaction();
		$this->assertTrue(self::$db->inTransaction());
		self::$db->rollBack();
		$this->assertFalse(self::$db->inTransaction());
	}

	// --------------------------------------------------------------------------

	/*public function testGetAttribute()
	{
		$res = self::$db->getAttribute("foo");
		$this->assertEquals(NULL, $res);
	}

	// --------------------------------------------------------------------------

	public function testSetAttribute()
	{
		$this->assertFalse(self::$db->setAttribute(47, 'foo'));
	}*/

	public function testLastInsertId()
	{
$this->markTestSkipped();
		$this->assertEqual(0, self::$db->lastInsertId('NEWTABLE_SEQ'));
	}
}