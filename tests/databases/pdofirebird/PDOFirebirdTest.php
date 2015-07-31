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
		if (version_compare(PHP_VERSION, '7.0.0', '<='))
		{
			$this->markTestSkipped("Segfaults on this version of PHP");
		}

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
		if (version_compare(PHP_VERSION, '7.0.0', '<='))
		{
			$this->markTestSkipped("Segfaults on this version of PHP");
		}

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
		if (version_compare(PHP_VERSION, '7.0.0', '<='))
		{
			$this->markTestSkipped("Segfaults on this version of PHP");
		}

		self::$db->truncate('create_test');

		$this->assertTrue(self::$db->affected_rows() > 0);
	}

	// --------------------------------------------------------------------------

	public function testPreparedStatements()
	{
$this->markTestSkipped();
		/*if (version_compare(PHP_VERSION, '7.0.0', '<='))
		{
			$this->markTestSkipped("Segfaults on this version of PHP");
		}*/

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
$this->markTestSkipped();
		$sql = <<<SQL
			INSERT INTO "create_test" ("id", "key", "val")
			VALUES (?,?,?)
SQL;
		self::$db->prepare_execute($sql, array(
			2, "works", 'also?'
		));

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
		$this->assertEquals(00000, $result);
	}

	// --------------------------------------------------------------------------

	public function testDBList()
	{
		$res = self::$db->get_sql()->db_list();
		$this->assertNULL($res);
	}
}