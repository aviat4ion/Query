<?php declare(strict_types=1);
/**
 * Query
 *
 * SQL Query Builder / Database Abstraction Layer
 *
 * PHP version 8.1
 *
 * @package     Query
 * @author      Timothy J. Warren <tim@timshome.page>
 * @copyright   2012 - 2023 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link        https://git.timshomepage.net/aviat/Query
 * @version     4.0.0
 */

namespace Query\Tests\Drivers\SQLite;

use PDO;
use Query\Drivers\Sqlite\Driver;
use Query\Exception\NotImplementedException;
use Query\Tests\BaseDriverTest;

/**
 * SQLiteTest class.
 *
 * @extends DBTest
 * @requires extension pdo_sqlite
 */
class SQLiteDriverTest extends BaseDriverTest
{
	public static function setupBeforeClass(): void
	{
		$params = [
			'type' => 'sqlite',
			'file' => ':memory:',
			'prefix' => 'create_',
			'alias' => 'test_sqlite',
			'options' => [
				PDO::ATTR_PERSISTENT => TRUE,
			],
		];

		self::$db = Query($params);
		self::$db->setTablePrefix('create_');
	}

	// --------------------------------------------------------------------------
	// ! Util Method tests
	// --------------------------------------------------------------------------

	public function testCreateTable(): void
	{
		self::$db->exec(file_get_contents(QTEST_DIR . '/db_files/sqlite.sql'));

		//Check
		$dbs = self::$db->getTables();

		$this->assertTrue(\in_array('TEST1', $dbs, TRUE));
		$this->assertTrue(\in_array('TEST2', $dbs, TRUE));
		$this->assertTrue(\in_array('NUMBERS', $dbs, TRUE));
		$this->assertTrue(\in_array('NEWTABLE', $dbs, TRUE));
		$this->assertTrue(\in_array('create_test', $dbs, TRUE));
		$this->assertTrue(\in_array('create_join', $dbs, TRUE));
		$this->assertTrue(\in_array('create_delete', $dbs, TRUE));
	}

	/*public function testBackupData()
	{
		$sql = mb_trim(self::$db->getUtil()->backupData(['create_join', 'create_test']));

		$sqlArray = explode("\n", $sql);

		$expected = <<<SQL
INSERT INTO "create_test" ("id","key","val") VALUES (1,'boogers','Gross');
INSERT INTO "create_test" ("id","key","val") VALUES (2,'works','also?');
INSERT INTO "create_test" ("id","key","val") VALUES (10,12,14);
INSERT INTO "create_test" ("id","key","val") VALUES (587,1,2);
INSERT INTO "create_test" ("id","key","val") VALUES (999,'''ring''','''sale''');
SQL;
		$expectedArray = explode("\n", $expected);
		$this->assertEquals($expectedArray, $sqlArray);
	}*/

	public function testBackupStructure(): void
	{
		$sql = mb_trim(self::$db->getUtil()->backupStructure());
		$expected = <<<'SQL'
CREATE TABLE "create_test" ("id" INTEGER PRIMARY KEY AUTOINCREMENT, "key" TEXT, "val" TEXT);
CREATE TABLE sqlite_sequence(name,seq);
CREATE TABLE "create_join" ("id" INTEGER PRIMARY KEY, "key" TEXT, "val" TEXT);
CREATE TABLE "create_delete" ("id" INTEGER PRIMARY KEY, "key" TEXT, "val" TEXT);
CREATE TABLE TEST1 (
  TEST_NAME TEXT NOT NULL,
  TEST_ID INTEGER DEFAULT '0' NOT NULL,
  TEST_DATE TEXT NOT NULL,
  CONSTRAINT PK_TEST PRIMARY KEY (TEST_ID)
);
CREATE TABLE TEST2 (
  ID INTEGER NOT NULL,
  FIELD1 INTEGER,
  FIELD2 TEXT,
  FIELD3 TEXT,
  FIELD4 INTEGER,
  FIELD5 INTEGER,
  ID2 INTEGER NOT NULL,
  CONSTRAINT PK_TEST2 PRIMARY KEY (ID2),
  CONSTRAINT TEST2_FIELD1ID_IDX UNIQUE (ID, FIELD1),
  CONSTRAINT TEST2_FIELD4_IDX UNIQUE (FIELD4)
);
;
;
CREATE INDEX TEST2_FIELD5_IDX ON TEST2 (FIELD5);
CREATE TABLE NUMBERS (
  NUMBER INTEGER DEFAULT 0 NOT NULL,
  EN TEXT NOT NULL,
  FR TEXT NOT NULL
);
CREATE TABLE NEWTABLE (
  ID INTEGER DEFAULT 0 NOT NULL,
  SOMENAME TEXT,
  SOMEDATE TEXT NOT NULL,
  CONSTRAINT PKINDEX_IDX PRIMARY KEY (ID)
);
CREATE VIEW "testview" AS
SELECT *
FROM TEST1
WHERE TEST_NAME LIKE 't%';
CREATE VIEW "numbersview" AS
SELECT *
FROM NUMBERS
WHERE NUMBER > 100;
CREATE TABLE "testconstraints" (
  someid integer NOT NULL,
  somename TEXT NOT NULL,
  CONSTRAINT testconstraints_id_pk PRIMARY KEY (someid)
);
CREATE TABLE "testconstraints2" (
  ext_id integer NOT NULL,
  modified text,
  uniquefield text NOT NULL,
  usraction integer NOT NULL,
  CONSTRAINT testconstraints_id_fk FOREIGN KEY (ext_id)
      REFERENCES testconstraints (someid)
      ON UPDATE CASCADE
	  ON DELETE CASCADE,
  CONSTRAINT unique_2_fields_idx UNIQUE (modified, usraction),
  CONSTRAINT uniquefld_idx UNIQUE (uniquefield)
);
;
;
SQL;

		$expectedArray = explode("\n", $expected);
		$resultArray = explode("\n", $sql);

		$this->assertEquals($expectedArray, $resultArray);
	}

	public function testDeleteTable(): void
	{
		$sql = self::$db->getUtil()->deleteTable('create_delete');

		self::$db->query($sql);

		//Check
		$dbs = self::$db->getTables();
		$this->assertFalse(in_array('create_delete', $dbs, TRUE));
	}

	// --------------------------------------------------------------------------
	// ! General tests
	// --------------------------------------------------------------------------

	public function testConnection(): void
	{
		$class = Driver::class;

		$db = new $class(QTEST_DIR . QDS . 'db_files' . QDS . 'test_sqlite.db');

		$this->assertIsA($db, $class);

		unset($db);
	}

	public function testTruncate(): void
	{
		self::$db->truncate('create_test');
		$this->assertEquals(0, self::$db->countAll('create_test'));
	}

	public function testPreparedStatements(): void
	{
		$sql = <<<'SQL'
			INSERT INTO "create_test" ("id", "key", "val")
			VALUES (?,?,?)
SQL;
		$statement = self::$db->prepareQuery($sql, [1, 'boogers', 'Gross']);

		$statement->execute();

		$res = self::$db->query('SELECT * FROM "create_test" WHERE "id"=1')
			->fetch(PDO::FETCH_ASSOC);

		$this->assertEquals([
			'id' => 1,
			'key' => 'boogers',
			'val' => 'Gross',
		], $res);
	}

	public function testPrepareExecute(): void
	{
		$sql = <<<'SQL'
			INSERT INTO "create_test" ("id", "key", "val")
			VALUES (?,?,?)
SQL;
		self::$db->prepareExecute($sql, [
			2, 'works', 'also?',
		]);

		$res = self::$db->query('SELECT * FROM "create_test" WHERE "id"=2')
			->fetch(PDO::FETCH_ASSOC);

		$this->assertEquals([
			'id' => 2,
			'key' => 'works',
			'val' => 'also?',
		], $res);
	}

	public function testCommitTransaction(): void
	{
		$res = self::$db->beginTransaction();

		$sql = 'INSERT INTO "create_test" ("id", "key", "val") VALUES (10, 12, 14)';
		self::$db->query($sql);

		$res = self::$db->commit();
		$this->assertTrue($res);
	}

	public function testRollbackTransaction(): void
	{
		$res = self::$db->beginTransaction();

		$sql = 'INSERT INTO "create_test" ("id", "key", "val") VALUES (182, 96, 43)';
		self::$db->query($sql);

		$res = self::$db->rollback();
		$this->assertTrue($res);
	}

	public function testGetDBs(): void
	{
		$driverSQL = self::$db->getSql()->dbList();
		$this->assertEquals('', $driverSQL);

		$this->assertNull(self::$db->getDbs());
	}

	public function testGetSchemas(): void
	{
		$this->assertNull(self::$db->getSchemas());
	}

	// --------------------------------------------------------------------------
	// ! SQL tests
	// --------------------------------------------------------------------------

	public function testGetSystemTables(): void
	{
		$sql = self::$db->getSystemTables();
		$this->assertIsArray($sql);
	}

	public function testGetSequences(): void
	{
		$sql = self::$db->getSequences();
		$this->assertEquals(['create_test'], $sql);
	}

	public function testGetFunctions(): void
	{
		$this->expectException(NotImplementedException::class);
		self::$db->getFunctions();
	}

	public function testGetProcedures(): void
	{
		$this->expectException(NotImplementedException::class);
		self::$db->getProcedures();
	}
}
