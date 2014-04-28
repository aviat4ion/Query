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

	abstract public function testConnection();

	// --------------------------------------------------------------------------

	public function tearDown()
	{
		$this->db = NULL;
	}

	// --------------------------------------------------------------------------

	public function testGetTables()
	{
		$tables = $this->db->get_tables();
		$this->assertTrue(is_array($tables));
		$this->assertTrue( ! empty($tables));
	}

	// --------------------------------------------------------------------------

	public function testGetSystemTables()
	{
		$tables = $this->db->get_system_tables();
		$this->assertTrue(is_array($tables));
		$this->assertTrue( ! empty($tables));
	}

	// --------------------------------------------------------------------------

	public function testBackupData()
	{
		$this->assertTrue(is_string($this->db->util->backup_data(array('create_delete', TRUE))));
	}

	// --------------------------------------------------------------------------

	public function testGetColumns()
	{
		$cols = $this->db->get_columns('test');
		$this->assertTrue(is_array($cols));
		$this->assertTrue( ! empty($cols));
	}

	// --------------------------------------------------------------------------

	public function testGetTypes()
	{
		$types = $this->db->get_types();
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

		$keys = $this->db->get_fks('testconstraints2');
		$this->assertEqual($expected, $keys);
	}

	// --------------------------------------------------------------------------

	public function testGetIndexes()
	{
		$keys = $this->db->get_indexes('test');
		$this->assertTrue(is_array($keys));
	}

	// --------------------------------------------------------------------------

	public function testGetViews()
	{
		$views = $this->db->get_views();
		$expected = array('numbersview', 'testview');
		$this->assertEqual($expected, array_values($views));
		$this->assertTrue(is_array($views));
	}

	// --------------------------------------------------------------------------

	public function testGetTriggers()
	{
		// @TODO standardize trigger output for different databases

		$triggers = $this->db->get_triggers();
		$this->assertTrue(is_array($triggers));
	}

	// --------------------------------------------------------------------------

	public function testGetSequences()
	{
		$seqs = $this->db->get_sequences();

		// Normalize sequence names
		$seqs = array_map('strtolower', $seqs);

		$expected = array('newtable_seq');

		$this->assertTrue(is_array($seqs));
		$this->assertEqual($expected, $seqs);
	}

	// --------------------------------------------------------------------------

	public function testGetProcedures()
	{
		$procedures = $this->db->get_procedures();
		$this->assertTrue(is_array($procedures));
	}

	// --------------------------------------------------------------------------

	public function testGetFunctions()
	{
		$funcs = $this->db->get_functions();
		$this->assertTrue(is_array($funcs));
	}
}
// End of db_test.php