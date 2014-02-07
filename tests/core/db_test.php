<?php
/**
 * Query
 *
 * Free Query Builder / Database Abstraction Layer
 *
 * @package		Query
 * @author		Timothy J. Warren
 * @copyright	Copyright (c) 2012 - 2013
 * @link 		https://github.com/aviat4ion/Query
 * @license		http://philsturgeon.co.uk/code/dbad-license
 */

// --------------------------------------------------------------------------

/**
 * Parent Database Test Class
 */
abstract class DBTest extends UnitTestCase {

	abstract public function TestConnection();
	
	// --------------------------------------------------------------------------

	public function tearDown()
	{
		$this->db = NULL;
	}
	
	// --------------------------------------------------------------------------

	public function TestGetTables()
	{
		if (empty($this->db))  return;

		$tables = $this->db->get_tables();
		$this->assertTrue(is_array($tables));
	}
	
	// --------------------------------------------------------------------------

	public function TestGetSystemTables()
	{
		if (empty($this->db))  return;

		$tables = $this->db->get_system_tables();

		$this->assertTrue(is_array($tables));
	}
	
	// --------------------------------------------------------------------------

	public function TestCreateTransaction()
	{
		if (empty($this->db))  return;

		$res = $this->db->beginTransaction();
		$this->assertTrue($res);
	}
	
	// --------------------------------------------------------------------------
	
	public function TestBackupData()
	{
		if (empty($this->db))  return;
		
		$this->assertTrue(is_string($this->db->util->backup_data()));
	}
	
	// --------------------------------------------------------------------------
	
	public function TestGetColumns()
	{
		if (empty($this->db))  return;
	
		$cols = $this->db->get_columns('create_test');
		$this->assertTrue(is_array($cols));
	}
	
	// --------------------------------------------------------------------------
	
	public function TestGetTypes()
	{
		if (empty($this->db))  return;
	
		$types = $this->db->get_types();
		$this->assertTrue(is_array($types));
	}
	
}
// End of db_test.php