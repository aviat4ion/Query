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
	}

	// --------------------------------------------------------------------------

	public function testGetSystemTables()
	{
		$tables = $this->db->get_system_tables();

		$this->assertTrue(is_array($tables));
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
	}

	// --------------------------------------------------------------------------

	public function testGetTypes()
	{
		$types = $this->db->get_types();
		$this->assertTrue(is_array($types));
	}

	// --------------------------------------------------------------------------

	public function testGetFKs()
	{
		$keys = $this->db->get_fks('create_test');
		$this->assertTrue(is_array($keys));
	}

	// --------------------------------------------------------------------------

	public function testGetIndexes()
	{
		$keys = $this->db->get_indexes('test');
		$this->assertTrue(is_array($keys));
	}

}
// End of db_test.php