<?php
/**
 * OpenSQLManager
 *
 * Free Database manager for Open Source Databases
 *
 * @author 		Timothy J. Warren
 * @copyright	Copyright (c) 2012 - 2013
 * @link 		https://github.com/aviat4ion/OpenSQLManager
 * @license 	http://philsturgeon.co.uk/code/dbad-license
 */

// --------------------------------------------------------------------------

/**
 * PgTest class.
 *
 * @extends DBTest
 * @requires extension pdo_pgsql
 */
class PgTest extends DBTest {

	public function setUp()
	{	
		// Attempt to connect, if there is a test config file
		if (is_file(QBASE_DIR . "test_config.json"))
		{
			$params = json_decode(file_get_contents(QBASE_DIR . "test_config.json"));
			$params = $params->pgsql;

			$this->db = new PgSQL("pgsql:host={$params->host};dbname={$params->database}", $params->user, $params->pass);
		}
		elseif (($var = getenv('CI')))
		{
			$this->db = new PgSQL('host=127.0.0.1;port=5432;dbname=test', 'postgres');
		}
	}
	
	// --------------------------------------------------------------------------

	public function testExists()
	{
		$this->assertTrue(in_array('pgsql', PDO::getAvailableDrivers()));
	}
	
	// --------------------------------------------------------------------------

	public function testConnection()
	{
		if (empty($this->db))  return;

		$this->assertIsA($this->db, 'PgSQL');
	}
	
	// --------------------------------------------------------------------------

	public function testCreateTable()
	{
		if (empty($this->db))  return;

		// Drop the table(s) if they exist
		$sql = 'DROP TABLE IF EXISTS "create_test"';
		$this->db->query($sql);
		$sql = 'DROP TABLE IF EXISTS "create_join"';
		$this->db->query($sql);


		//Attempt to create the table
		$sql = $this->db->util->create_table('create_test',
			array(
				'id' => 'integer',
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
				'id' => 'integer',
				'key' => 'TEXT',
				'val' => 'TEXT',
			),
			array(
				'id' => 'PRIMARY KEY'
			)
		);
		$this->db->query($sql);

		//echo $sql.'<br />';

		//Reset
		unset($this->db);
		$this->setUp();

		//Check
		$dbs = $this->db->get_tables();
		$this->assertTrue(in_array('create_test', $dbs));

	}
	
	// --------------------------------------------------------------------------
	
	public function testTruncate()
	{
		$this->db->truncate('create_test');
		$this->db->truncate('create_join');
		
		$ct_query = $this->db->query('SELECT * FROM create_test');
		$cj_query = $this->db->query('SELECT * FROM create_join');
	}
	
	// --------------------------------------------------------------------------
	
	public function testPreparedStatements()
	{
		if (empty($this->db))  return;

		$sql = <<<SQL
			INSERT INTO "create_test" ("id", "key", "val")
			VALUES (?,?,?)
SQL;
		$statement = $this->db->prepare_query($sql, array(1,"boogers", "Gross"));

		$statement->execute();

	}
	
	// --------------------------------------------------------------------------

	public function testPrepareExecute()
	{
		if (empty($this->db))  return;

		$sql = <<<SQL
			INSERT INTO "create_test" ("id", "key", "val")
			VALUES (?,?,?)
SQL;
		$this->db->prepare_execute($sql, array(
			2, "works", 'also?'
		));

	}
	
	// --------------------------------------------------------------------------

	public function testCommitTransaction()
	{
		if (empty($this->db))  return;

		$res = $this->db->beginTransaction();

		$sql = 'INSERT INTO "create_test" ("id", "key", "val") VALUES (10, 12, 14)';
		$this->db->query($sql);

		$res = $this->db->commit();
		$this->assertTrue($res);
	}
	
	// --------------------------------------------------------------------------

	public function testRollbackTransaction()
	{
		if (empty($this->db))  return;

		$res = $this->db->beginTransaction();

		$sql = 'INSERT INTO "create_test" ("id", "key", "val") VALUES (182, 96, 43)';
		$this->db->query($sql);

		$res = $this->db->rollback();
		$this->assertTrue($res);
	}
	
	// --------------------------------------------------------------------------
	
	public function testGetSchemas()
	{
		$this->assertTrue(is_array($this->db->get_schemas()));
	}
	
	// --------------------------------------------------------------------------
	
	public function testGetSequences()
	{
		$this->assertTrue(is_array($this->db->get_sequences()));
	}
	
	// --------------------------------------------------------------------------
	
	public function testGetsProcedures()
	{
		$this->assertTrue(is_array($this->db->get_procedures()));
	}
	
	// --------------------------------------------------------------------------
	
	public function testGetTriggers()
	{
		$this->assertTrue(is_array($this->db->get_triggers()));
	}
}