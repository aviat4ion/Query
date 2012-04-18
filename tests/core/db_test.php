<?php
/**
 * Query
 *
 * Free Query Builder / Database Abstraction Layer
 *
 * @author 		Timothy J. Warren
 * @copyright	Copyright (c) 2012
 * @link 		https://github.com/aviat4ion/Query
 * @license 	http://philsturgeon.co.uk/code/dbad-license 
 */

// --------------------------------------------------------------------------

/**
 * Parent Database Test Class
 */
abstract class DBTest extends UnitTestCase {

	abstract function TestConnection();

	function tearDown()
	{
		$this->db = NULL;
	}

	function TestGetTables()
	{
		if (empty($this->db))  return;

		$tables = $this->db->get_tables();
		$this->assertTrue(is_array($tables));
	}

	function TestGetSystemTables()
	{
		if (empty($this->db))  return;

		$tables = $this->db->get_system_tables();

		$this->assertTrue(is_array($tables));
	}

	function TestCreateTransaction()
	{
		if (empty($this->db))  return;

		$res = $this->db->beginTransaction();
		$this->assertTrue($res);
	}
	
	function TestBackupData()
	{
		$this->assertTrue(is_string($this->db->util->backup_data()));
	}
}
// End of db_test.php