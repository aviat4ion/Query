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
 * MySQLTest class.
 *
 * @extends DBTest
 * @requires extension pdo_mysql
 */
class MySQLTest extends DBTest {

	public function setUp()
	{
		// Attempt to connect, if there is a test config file
		if (is_file(QBASE_DIR . "test_config.json"))
		{
			$params = json_decode(file_get_contents(QBASE_DIR . "test_config.json"));
			$params = $params->mysql;

			$this->db = new MySQL("mysql:host={$params->host};dbname={$params->database}", $params->user, $params->pass, array(
				PDO::ATTR_PERSISTENT => TRUE
			));
		}
		elseif (($var = getenv('CI')))
		{
			$this->db = new MySQL('host=127.0.0.1;port=3306;dbname=test', 'root');
		}
	}
	
	// --------------------------------------------------------------------------

	public function testExists()
	{
		$this->assertTrue(in_array('mysql', PDO::getAvailableDrivers()));
	}
	
	// --------------------------------------------------------------------------

	public function testConnection()
	{
		$this->assertIsA($this->db, 'MySQL');
	}
	
	// --------------------------------------------------------------------------

	public function testCreateTable()
	{
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
	
	public function testTruncate()
	{
		$this->db->truncate('create_test');
		$this->db->truncate('create_join');
	}
	
	// --------------------------------------------------------------------------
	
	public function testPreparedStatements()
	{
		$sql = <<<SQL
			INSERT INTO `create_test` (`id`, `key`, `val`)
			VALUES (?,?,?)
SQL;
		$statement = $this->db->prepare_query($sql, array(1,"boogers", "Gross"));

		$res = $statement->execute();
		
		$this->assertTrue($res);

	}
	
	// --------------------------------------------------------------------------

	public function testPrepareExecute()
	{
		$sql = <<<SQL
			INSERT INTO `create_test` (`id`, `key`, `val`)
			VALUES (?,?,?)
SQL;
		$res = $this->db->prepare_execute($sql, array(
			2, "works", 'also?'
		));
		
		$this->assertInstanceOf('PDOStatement', $res);

	}
	
	// --------------------------------------------------------------------------

	public function testCommitTransaction()
	{
		$res = $this->db->beginTransaction();

		$sql = 'INSERT INTO `create_test` (`id`, `key`, `val`) VALUES (10, 12, 14)';
		$this->db->query($sql);

		$res = $this->db->commit();
		$this->assertTrue($res);
	}
	
	// --------------------------------------------------------------------------

	public function testRollbackTransaction()
	{
		$res = $this->db->beginTransaction();

		$sql = 'INSERT INTO `create_test` (`id`, `key`, `val`) VALUES (182, 96, 43)';
		$this->db->query($sql);

		$res = $this->db->rollback();
		$this->assertTrue($res);
	}
	
	// --------------------------------------------------------------------------
	
	public function testGetSchemas()
	{
		$this->assertNull($this->db->get_schemas());
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
	
	// --------------------------------------------------------------------------
	
	public function testGetSequences()
	{
		$this->assertNull($this->db->get_sequences());
	}
	
	public function testBackup()
	{
		$this->assertTrue(is_string($this->db->util->backup_structure()));
	}
}