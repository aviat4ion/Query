<?php declare(strict_types=1);
/**
 * Query
 *
 * SQL Query Builder / Database Abstraction Layer
 *
 * PHP version 7.1
 *
 * @package     Query
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2012 - 2018 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link        https://git.timshomepage.net/aviat4ion/Query
 */
namespace Query\Tests;

use Query\QueryBuilderInterface;

/**
 * Parent Database Test Class
 */
abstract class BaseDriverTest extends TestCase {

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
		$this->assertTrue(\is_array($tables));
		$this->assertTrue( ! empty($tables));
	}

	public function testGetSystemTables(): void
	{
		$tables = self::$db->getSystemTables();
		$this->assertTrue(\is_array($tables));
		$this->assertTrue( ! empty($tables));
	}

	public function testBackupData(): void
	{
		$this->assertTrue(\is_string(self::$db->getUtil()->backupData(['create_delete', FALSE])));
		$this->assertTrue(\is_string(self::$db->getUtil()->backupData(['create_delete', TRUE])));
	}

	public function testGetColumns(): void
	{
		$cols = self::$db->getColumns('test');
		$this->assertTrue(\is_array($cols));
		$this->assertTrue( ! empty($cols));
	}

	public function testGetTypes(): void
	{
		$types = self::$db->getTypes();
		$this->assertTrue(\is_array($types));
		$this->assertTrue( ! empty($types));
	}

	public function testGetFKs(): void
	{
		$expected = [[
			'child_column' => 'ext_id',
			'parent_table' => 'testconstraints',
			'parent_column' => 'someid',
			'update' => 'CASCADE',
			'delete' => 'CASCADE'
		]];

		$keys = self::$db->getFks('testconstraints2');
		$this->assertEqual($expected, $keys);
	}

	public function testGetIndexes(): void
	{
		$keys = self::$db->getIndexes('test');
		$this->assertTrue(\is_array($keys));
	}

	public function testGetViews(): void
	{
		$views = self::$db->getViews();
		$expected = ['numbersview', 'testview'];
		$this->assertEqual($expected, array_values($views));
		$this->assertTrue(\is_array($views));
	}

	public function testGetTriggers(): void
	{
		// @TODO standardize trigger output for different databases

		$triggers = self::$db->getTriggers();
		$this->assertTrue(\is_array($triggers));
	}

	public function testGetSequences(): void
	{
		$seqs = self::$db->getSequences();

		// Normalize sequence names
		$seqs = array_map('strtolower', $seqs);

		$expected = ['newtable_seq'];

		$this->assertTrue(\is_array($seqs));
		$this->assertEqual($expected, $seqs);
	}

	public function testGetProcedures(): void
	{
		$procedures = self::$db->getProcedures();
		$this->assertTrue(\is_array($procedures));
	}

	public function testGetFunctions(): void
	{
		$funcs = self::$db->getFunctions();
		$this->assertTrue(\is_array($funcs));
	}
}
// End of db_test.php