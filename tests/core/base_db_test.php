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
 * Parent Database Test Class
 */
abstract class DBTest extends Query_TestCase {

	protected static $db = NULL;

	abstract public function testConnection();

	// --------------------------------------------------------------------------

	public static function tearDownAfterClass()
	{
		self::$db = NULL;
	}

	// --------------------------------------------------------------------------

	public function testGetTables()
	{
		$tables = self::$db->get_tables();
		$this->assertTrue(is_array($tables));
		$this->assertTrue( ! empty($tables));
	}

	// --------------------------------------------------------------------------

	public function testGetSystemTables()
	{
		$tables = self::$db->get_system_tables();
		$this->assertTrue(is_array($tables));
		$this->assertTrue( ! empty($tables));
	}

	// --------------------------------------------------------------------------

	public function testBackupData()
	{
		$this->assertTrue(is_string(self::$db->get_util()->backup_data(array('create_delete', FALSE))));
		$this->assertTrue(is_string(self::$db->get_util()->backup_data(array('create_delete', TRUE))));
	}

	// --------------------------------------------------------------------------

	public function testGetColumns()
	{
		$cols = self::$db->get_columns('test');
		$this->assertTrue(is_array($cols));
		$this->assertTrue( ! empty($cols));
	}

	// --------------------------------------------------------------------------

	public function testGetTypes()
	{
		$types = self::$db->get_types();
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

		$keys = self::$db->get_fks('testconstraints2');
		$this->assertEqual($expected, $keys);
	}

	// --------------------------------------------------------------------------

	public function testGetIndexes()
	{
		$keys = self::$db->get_indexes('test');
		$this->assertTrue(is_array($keys));
	}

	// --------------------------------------------------------------------------

	public function testGetViews()
	{
		$views = self::$db->get_views();
		$expected = array('numbersview', 'testview');
		$this->assertEqual($expected, array_values($views));
		$this->assertTrue(is_array($views));
	}

	// --------------------------------------------------------------------------

	public function testGetTriggers()
	{
		// @TODO standardize trigger output for different databases

		$triggers = self::$db->get_triggers();
		$this->assertTrue(is_array($triggers));
	}

	// --------------------------------------------------------------------------

	public function testGetSequences()
	{
		$seqs = self::$db->get_sequences();

		// Normalize sequence names
		$seqs = array_map('strtolower', $seqs);

		$expected = array('newtable_seq');

		$this->assertTrue(is_array($seqs));
		$this->assertEqual($expected, $seqs);
	}

	// --------------------------------------------------------------------------

	public function testGetProcedures()
	{
		$procedures = self::$db->get_procedures();
		$this->assertTrue(is_array($procedures));
	}

	// --------------------------------------------------------------------------

	public function testGetFunctions()
	{
		$funcs = self::$db->get_functions();
		$this->assertTrue(is_array($funcs));
	}
}
// End of db_test.php