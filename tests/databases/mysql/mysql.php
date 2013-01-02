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
 * MySQLTest class.
 *
 * @extends UnitTestCase
 */
class MySQLTest extends DBTest {

	public function setUp()
	{
		// Attempt to connect, if there is a test config file
		if (is_file(QBASE_DIR . "test_config.json"))
		{
			$params = json_decode(file_get_contents(QBASE_DIR . "test_config.json"));
			$params = $params->mysql;

			$this->db = new MySQL("mysql:host={$params->host};dbname={$params->database}", $params->user, $params->pass);
		}
		elseif (($var = getenv('CI')))
		{
			$this->db = new MySQL('host=127.0.0.1;port=3306;dbname=test', 'root');
		}
	}
	
	// --------------------------------------------------------------------------

	public function TestExists()
	{
		$this->assertTrue(in_array('mysql', PDO::getAvailableDrivers()));
	}
	
	// --------------------------------------------------------------------------

	public function TestConnection()
	{
		if (empty($this->db))  return;

		$this->assertIsA($this->db, 'MySQL');
	}
	
	// --------------------------------------------------------------------------

	public function TestCreateTable()
	{
		if (empty($this->db))  return;

		//Attempt to create the table
		$sql = $this->db->util->create_table('create_test',
			array(
				'id' => 'int(10)',
				'key' => 'TEXT',
				'val' => 'TEXT',
			),
			array(
				'id' => 'PRIMARY KEY'
			)
		);

		$this->db->query($sql);

		//Attempt to create the table
		$sql = $this->db->util->create_table('create_join',
			array(
				'id' => 'int(10)',
				'key' => 'TEXT',
				'val' => 'TEXT',
			),
			array(
				'id' => 'PRIMARY KEY'
			)
		);
		$this->db->query($sql);

		//Check
		$dbs = $this->db->get_tables();

		$this->assertTrue(in_array('create_test', $dbs));

	}
	
	// --------------------------------------------------------------------------
	
	public function TestTruncate()
	{
		$this->db->truncate('create_test');
		$this->db->truncate('create_join');
		
		$ct_query = $this->db->query('SELECT * FROM create_test');
		$cj_query = $this->db->query('SELECT * FROM create_join');
	}
	
	// --------------------------------------------------------------------------
	
	public function TestPreparedStatements()
	{
		if (empty($this->db))  return;

		$sql = <<<SQL
			INSERT INTO `create_test` (`id`, `key`, `val`)
			VALUES (?,?,?)
SQL;
		$statement = $this->db->prepare_query($sql, array(1,"boogers", "Gross"));

		$statement->execute();

	}
	
	// --------------------------------------------------------------------------

	public function TestPrepareExecute()
	{
		if (empty($this->db))  return;

		$sql = <<<SQL
			INSERT INTO `create_test` (`id`, `key`, `val`)
			VALUES (?,?,?)
SQL;
		$this->db->prepare_execute($sql, array(
			2, "works", 'also?'
		));

	}
	
	// --------------------------------------------------------------------------

	public function TestCommitTransaction()
	{
		if (empty($this->db))  return;

		$res = $this->db->beginTransaction();

		$sql = 'INSERT INTO `create_test` (`id`, `key`, `val`) VALUES (10, 12, 14)';
		$this->db->query($sql);

		$res = $this->db->commit();
		$this->assertTrue($res);
	}
	
	// --------------------------------------------------------------------------

	public function TestRollbackTransaction()
	{
		if (empty($this->db))  return;

		$res = $this->db->beginTransaction();

		$sql = 'INSERT INTO `create_test` (`id`, `key`, `val`) VALUES (182, 96, 43)';
		$this->db->query($sql);

		$res = $this->db->rollback();
		$this->assertTrue($res);
	}
	
	// --------------------------------------------------------------------------
	
	public function TestGetSchemas()
	{
		$this->assertFalse($this->db->get_schemas());
	}
	
	// --------------------------------------------------------------------------
	
	public function TestGetsProcedures()
	{
		$this->assertTrue(is_array($this->db->get_procedures()));
	}
	
	// --------------------------------------------------------------------------
	
	public function TestGetTriggers()
	{
		$this->assertTrue(is_array($this->db->get_triggers()));
	}
	
	// --------------------------------------------------------------------------
	
	public function TestGetSequences()
	{
		$this->assertFalse($this->db->get_sequences());
	}
	
	public function TestBackup()
	{
		$this->assertTrue(is_string($this->db->util->backup_structure()));
	}
}