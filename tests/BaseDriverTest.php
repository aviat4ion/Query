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

// --------------------------------------------------------------------------

/**
 * Parent Database Test Class
 */
abstract class BaseDriverTest extends TestCase {

	/**
	 * @var \Query\QueryBuilder
	 */
	protected static $db;

	abstract public function testConnection();

	// --------------------------------------------------------------------------

	public static function tearDownAfterClass()
	{
		self::$db = NULL;
	}

	// --------------------------------------------------------------------------

	public function testGetTables()
	{
		$tables = self::$db->getTables();
		$this->assertTrue(is_array($tables));
		$this->assertTrue( ! empty($tables));
	}

	// --------------------------------------------------------------------------

	public function testGetSystemTables()
	{
		$tables = self::$db->getSystemTables();
		$this->assertTrue(is_array($tables));
		$this->assertTrue( ! empty($tables));
	}

	// --------------------------------------------------------------------------

	public function testBackupData()
	{
		$this->assertTrue(is_string(self::$db->getUtil()->backupData(array('create_delete', FALSE))));
		$this->assertTrue(is_string(self::$db->getUtil()->backupData(array('create_delete', TRUE))));
	}

	// --------------------------------------------------------------------------

	public function testGetColumns()
	{
		$cols = self::$db->getColumns('test');
		$this->assertTrue(is_array($cols));
		$this->assertTrue( ! empty($cols));
	}

	// --------------------------------------------------------------------------

	public function testGetTypes()
	{
		$types = self::$db->getTypes();
		$this->assertTrue(is_array($types));
		$this->assertTrue( ! empty($types));
	}

	// --------------------------------------------------------------------------

	public function testGetFKs()
	{
		$expected = array(array(
			'child_column' => 'ext_id',
			'parent_table' => 'testconstraints',
			'parent_column' => 'someid',
			'update' => 'CASCADE',
			'delete' => 'CASCADE'
		));

		$keys = self::$db->getFks('testconstraints2');
		$this->assertEqual($expected, $keys);
	}

	// --------------------------------------------------------------------------

	public function testGetIndexes()
	{
		$keys = self::$db->getIndexes('test');
		$this->assertTrue(is_array($keys));
	}

	// --------------------------------------------------------------------------

	public function testGetViews()
	{
		$views = self::$db->getViews();
		$expected = array('numbersview', 'testview');
		$this->assertEqual($expected, array_values($views));
		$this->assertTrue(is_array($views));
	}

	// --------------------------------------------------------------------------

	public function testGetTriggers()
	{
		// @TODO standardize trigger output for different databases

		$triggers = self::$db->getTriggers();
		$this->assertTrue(is_array($triggers));
	}

	// --------------------------------------------------------------------------

	public function testGetSequences()
	{
		$seqs = self::$db->getSequences();

		// Normalize sequence names
		$seqs = array_map('strtolower', $seqs);

		$expected = array('newtable_seq');

		$this->assertTrue(is_array($seqs));
		$this->assertEqual($expected, $seqs);
	}

	// --------------------------------------------------------------------------

	public function testGetProcedures()
	{
		$procedures = self::$db->getProcedures();
		$this->assertTrue(is_array($procedures));
	}

	// --------------------------------------------------------------------------

	public function testGetFunctions()
	{
		$funcs = self::$db->getFunctions();
		$this->assertTrue(is_array($funcs));
	}
}
// End of db_test.php