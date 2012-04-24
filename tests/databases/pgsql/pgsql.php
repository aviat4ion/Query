<?php
/**
 * OpenSQLManager
 *
 * Free Database manager for Open Source Databases
 *
 * @author 		Timothy J. Warren
 * @copyright	Copyright (c) 2012
 * @link 		https://github.com/aviat4ion/OpenSQLManager
 * @license 	http://philsturgeon.co.uk/code/dbad-license
 */

// --------------------------------------------------------------------------

/**
 * PgTest class.
 *
 * @extends UnitTestCase
 */
class PgTest extends DBTest {

	public function __construct()
	{
		parent::__construct();
	}
	
	// --------------------------------------------------------------------------

	public function setUp()
	{
		// Attempt to connect, if there is a test config file
		if (is_file(BASE_DIR . "test_config.json"))
		{
			$params = json_decode(file_get_contents(BASE_DIR . "test_config.json"));
			$params = $params->pgsql;

			$this->db = new PgSQL("host={$params->host};port={$params->port};dbname={$params->database}", $params->user, $params->pass);
		}
		elseif (($var = getenv('CI')))
		{
			$this->db = new PgSQL('host=127.0.0.1;port=5432;dbname=test', 'postgres');
		}
	}
	
	// --------------------------------------------------------------------------

	public function TestExists()
	{
		$this->assertTrue(in_array('pgsql', pdo_drivers()));
	}
	
	// --------------------------------------------------------------------------

	public function TestConnection()
	{
		if (empty($this->db))  return;

		$this->assertIsA($this->db, 'PgSQL');
	}
	
	// --------------------------------------------------------------------------

	public function TestCreateTable()
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
			INSERT INTO "create_test" ("id", "key", "val")
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
			INSERT INTO "create_test" ("id", "key", "val")
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

		$sql = 'INSERT INTO "create_test" ("id", "key", "val") VALUES (10, 12, 14)';
		$this->db->query($sql);

		$res = $this->db->commit();
		$this->assertTrue($res);
	}
	
	// --------------------------------------------------------------------------

	public function TestRollbackTransaction()
	{
		if (empty($this->db))  return;

		$res = $this->db->beginTransaction();

		$sql = 'INSERT INTO "create_test" ("id", "key", "val") VALUES (182, 96, 43)';
		$this->db->query($sql);

		$res = $this->db->rollback();
		$this->assertTrue($res);
	}
	
	// --------------------------------------------------------------------------
	
	public function TestGetSchemas()
	{
		$this->assertTrue(is_array($this->db->get_schemas()));
	}
	
	// --------------------------------------------------------------------------
	
	public function TestGetSequences()
	{
		$this->assertTrue(is_array($this->db->get_sequences()));
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
}