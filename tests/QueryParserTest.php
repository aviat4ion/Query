<?php declare(strict_types=1);
/**
 * Query
 *
 * SQL Query Builder / Database Abstraction Layer
 *
 * PHP version 7.1
 *
 * @package     Query
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2012 - 2018 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link        https://git.timshomepage.net/aviat4ion/Query
 */
namespace Query\Tests;

use Query\QueryParser;
use Query\Drivers\Sqlite\Driver;

/**
 * Tests for the Query Parser
 */
class QueryParserTest extends TestCase {
	/**
	 * @var QueryParser
	 */
	protected $parser;

	public function setUp()
	{
		$db = new Driver('sqlite::memory:');
		$this->parser = new QueryParser($db);
	}

	public function testGeneric()
	{
		$matches = $this->parser->parseJoin('table1.field1=table2.field2');
		$this->assertEqual($matches['combined'], array(
			'table1.field1', '=', 'table2.field2'
		));
	}

	public function testGeneric2()
	{
		$matches = $this->parser->parseJoin('db1.table1.field1!=db2.table2.field2');
		$this->assertEqual($matches['combined'], array(
			'db1.table1.field1','!=','db2.table2.field2'
		));
	}

	public function testWUnderscore()
	{
		$matches = $this->parser->parseJoin('table_1.field1 = tab_le2.field_2');
		$this->assertEqual($matches['combined'], array(
			'table_1.field1', '=', 'tab_le2.field_2'
		));
	}

	public function testFunction()
	{
		$matches = $this->parser->parseJoin('table1.field1 > SUM(3+5)');
		$this->assertEqual($matches['combined'], array(
			'table1.field1', '>', 'SUM(3+5)'
		));
	}
}