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
 * Tests for the Query Parser
 */
class QPTest extends Query_TestCase {

	public function setUp()
	{
		$this->parser = new Query\Query_Parser();
	}

	public function TestGeneric()
	{
		$matches = $this->parser->parse_join('table1.field1=table2.field2');
		$this->assertIdentical($matches['combined'], array(
			'table1.field1', '=', 'table2.field2'
		));
	}

	public function testGeneric2()
	{
		$matches = $this->parser->parse_join('db1.table1.field1!=db2.table2.field2');
		$this->assertIdentical($matches['combined'], array(
			'db1.table1.field1','!=','db2.table2.field2'
		));
	}

	public function testWUnderscore()
	{
		$matches = $this->parser->parse_join('table_1.field1 = tab_le2.field_2');
		$this->assertIdentical($matches['combined'], array(
			'table_1.field1', '=', 'tab_le2.field_2'
		));
	}

	public function testFunction()
	{
		$matches = $this->parser->parse_join('table1.field1 > SUM(3+5)');
		$this->assertIdentical($matches['combined'], array(
			'table1.field1', '>', 'SUM(3+5)'
		));
	}
}