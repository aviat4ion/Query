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

namespace Query\Tests;

use Query\QueryBuilderInterface;

/**
 * Parent Database Test Class
 */
abstract class BaseDriverTest extends BaseTestCase
{
	/**
	 * @var QueryBuilderInterface|null
	 */
	protected static $db;

	abstract public function testConnection();

	public static function tearDownAfterClass(): void
	{
		self::$db = NULL;
	}

	public function testGetTables(): void
	{
		$tables = self::$db->getTables();
		$this->assertIsArray($tables);
		$this->assertTrue( ! empty($tables));
	}

	public function testGetSystemTables(): void
	{
		$tables = self::$db->getSystemTables();
		$this->assertIsArray($tables);
		$this->assertTrue( ! empty($tables));
	}

	public function testBackupData(): void
	{
		$this->assertIsString(self::$db->getUtil()->backupData(['create_delete', FALSE]));
		$this->assertIsString(self::$db->getUtil()->backupData(['create_delete', TRUE]));
	}

	public function testGetColumns(): void
	{
		$cols = self::$db->getColumns('test');
		$this->assertIsArray($cols);
		$this->assertTrue( ! empty($cols));
	}

	public function testGetTypes(): void
	{
		$types = self::$db->getTypes();
		$this->assertIsArray($types);
		$this->assertTrue( ! empty($types));
	}

	public function testGetFKs(): void
	{
		$expected = [[
			'child_column' => 'ext_id',
			'parent_table' => 'testconstraints',
			'parent_column' => 'someid',
			'update' => 'CASCADE',
			'delete' => 'CASCADE',
		]];

		$keys = self::$db->getFks('testconstraints2');
		$this->assertEquals($expected, $keys);
	}

	public function testGetIndexes(): void
	{
		$keys = self::$db->getIndexes('test');
		$this->assertIsArray($keys);
	}

	public function testGetViews(): void
	{
		$views = self::$db->getViews();

		$this->assertIsArray($views);

		foreach (['numbersview', 'testview'] as $searchView)
		{
			$this->assertTrue(in_array($searchView, $views, TRUE));
		}
	}

	public function testGetTriggers(): void
	{
		$this->markTestSkipped('Deprecated');

		$triggers = self::$db->getTriggers();
		$this->assertIsArray($triggers);
	}

	public function testGetSequences(): void
	{
		$seqs = self::$db->getSequences();

		// Normalize sequence names
		$seqs = array_map('strtolower', $seqs);

		$expected = ['newtable_seq'];

		$this->assertIsArray($seqs);
		$this->assertEquals($expected, $seqs);
	}

	public function testGetProcedures(): void
	{
		$this->markTestSkipped('Deprecated');

		$procedures = self::$db->getProcedures();
		$this->assertIsArray($procedures);
	}

	public function testGetFunctions(): void
	{
		$this->markTestSkipped('Deprecated');

		$funcs = self::$db->getFunctions();
		$this->assertIsArray($funcs);
	}

	public function testGetVersion(): void
	{
		$version = self::$db->getVersion();
		$this->assertIsString($version);
		$this->assertTrue($version !== '');
	}
}
// End of db_test.php
