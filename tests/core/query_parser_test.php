<?php declare(strict_types=1);
/**
 * Query
 *
 * SQL Query Builder / Database Abstraction Layer
 *
 * PHP version 7
 *
 * @package     Query
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2012 - 2016 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link        https://git.timshomepage.net/aviat4ion/Query
 */


// --------------------------------------------------------------------------

/**
 * Tests for the Query Parser
 */
class Query_Parser_Test extends Query_TestCase {

	public function setUp()
	{
		$db = new Query\Drivers\Sqlite\Driver("sqlite::memory:");
		$this->parser = new Query\QueryParser($db);
	}

	public function TestGeneric()
	{
		$matches = $this->parser->parse_join('table1.field1=table2.field2');
		$this->assertEqual($matches['combined'], array(
			'table1.field1', '=', 'table2.field2'
		));
	}

	public function testGeneric2()
	{
		$matches = $this->parser->parse_join('db1.table1.field1!=db2.table2.field2');
		$this->assertEqual($matches['combined'], array(
			'db1.table1.field1','!=','db2.table2.field2'
		));
	}

	public function testWUnderscore()
	{
		$matches = $this->parser->parse_join('table_1.field1 = tab_le2.field_2');
		$this->assertEqual($matches['combined'], array(
			'table_1.field1', '=', 'tab_le2.field_2'
		));
	}

	public function testFunction()
	{
		$matches = $this->parser->parse_join('table1.field1 > SUM(3+5)');
		$this->assertEqual($matches['combined'], array(
			'table1.field1', '>', 'SUM(3+5)'
		));
	}
}