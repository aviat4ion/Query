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
namespace Query\Tests\Drivers\MySQL;

use PDO;
use Query\Drivers\Mysql\Driver;
use Query\Tests\BaseDriverTest;
use TypeError;

/**
 * MySQLTest class.
 *
 * @requires extension pdo_mysql
 */
class MySQLDriverTest extends BaseDriverTest {

	public static function setUpBeforeClass(): void
	{
		$params = get_json_config();
		if ($var = getenv('TRAVIS'))
		{
			self::$db = new Driver('host=127.0.0.1;port=3306;dbname=test', 'root');
		}
		// Attempt to connect, if there is a test config file
		else if ($params !== FALSE)
		{
			$params = $params->mysql;

			self::$db = new Driver("mysql:host={$params->host};dbname={$params->database}", $params->user, $params->pass, [
				PDO::ATTR_PERSISTENT => TRUE
			]);
		}

		self::$db->setTablePrefix('create_');
	}

	public function testExists(): void
	{
		$this->assertTrue(\in_array('mysql', PDO::getAvailableDrivers(), TRUE));
	}

	public function testConnection(): void
	{
		$this->assertIsA(self::$db, Driver::class);
	}

	public function testCreateTable(): void
	{
		self::$db->exec(file_get_contents(QTEST_DIR.'/db_files/mysql.sql'));

		//Attempt to create the table
		$sql = self::$db->getUtil()->createTable('test',
			[
				'id' => 'int(10)',
				'key' => 'TEXT',
				'val' => 'TEXT',
			],
			[
				'id' => 'PRIMARY KEY'
			],
		);

		self::$db->query($sql);

		//Attempt to create the table
		$sql = self::$db->getUtil()->createTable('join',
			[
				'id' => 'int(10)',
				'key' => 'TEXT',
				'val' => 'TEXT',
			],
			[
				'id' => 'PRIMARY KEY'
			]
		);
		self::$db->query($sql);

		//Check
		$dbs = self::$db->getTables();

		$this->assertTrue(\in_array('create_test', $dbs, TRUE));

	}


	public function testTruncate(): void
	{
		self::$db->truncate('test');
		$this->assertEquals(0, self::$db->countAll('test'));

		self::$db->truncate('join');
		$this->assertEquals(0, self::$db->countAll('join'));
	}

	public function testPreparedStatements(): void
	{
		$sql = <<<SQL
			INSERT INTO `create_test` (`id`, `key`, `val`)
			VALUES (?,?,?)
SQL;
		$statement = self::$db->prepareQuery($sql, [1, 'boogers', 'Gross']);

		$res = $statement->execute();

		$this->assertTrue($res);

	}

	public function testBadPreparedStatement(): void
	{
		if (is_a($this, \UnitTestCase::class))
		{
			$this->markTestSkipped();
			return;
		}

		$this->expectException(TypeError::class);

		$sql = <<<SQL
			INSERT INTO `create_test` (`id`, `key`, `val`)
			VALUES (?,?,?)
SQL;

		self::$db->prepareQuery($sql, 'foo');

	}

	public function testPrepareExecute(): void
	{
		$sql = <<<SQL
			INSERT INTO `create_test` (`id`, `key`, `val`)
			VALUES (?,?,?)
SQL;
		$res = self::$db->prepareExecute($sql, [
			2, 'works', 'also?'
		]);

		$this->assertInstanceOf('PDOStatement', $res);

	}

	public function testCommitTransaction(): void
	{
		// Make sure we aren't already in a transaction
		if (self::$db->inTransaction())
		{
			self::$db->commit();
		}

		$this->assertFalse(self::$db->inTransaction());
		$this->assertTrue(self::$db->beginTransaction());

		$sql = 'INSERT INTO `create_test` (`id`, `key`, `val`) VALUES (10, 12, 14)';
		self::$db->query($sql);

		$res = self::$db->commit();
		$this->assertTrue($res);
	}

	public function testRollbackTransaction(): void
	{
		// Make sure we aren't already in a transaction
		if (self::$db->inTransaction())
		{
			self::$db->commit();
		}

		$this->assertFalse(self::$db->inTransaction());
		$this->assertTrue(self::$db->beginTransaction());

		$sql = 'INSERT INTO `create_test` (`id`, `key`, `val`) VALUES (182, 96, 43)';
		self::$db->query($sql);

		$res = self::$db->rollback();
		$this->assertTrue($res);
	}

	public function testGetSchemas(): void
	{
		$this->assertTrue(in_array('test', self::$db->getSchemas()));
	}

	public function testGetSequences(): void
	{
		$this->assertNull(self::$db->getSequences());
	}

	public function testBackup(): void
	{
		$this->assertTrue(\is_string(self::$db->getUtil()->backupStructure()));
	}
}