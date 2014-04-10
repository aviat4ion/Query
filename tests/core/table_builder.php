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
 * Parent Table Builder Test Class
 */
abstract class TableBuilderTest extends Query_TestCase {

	public function testExists()
	{
		$this->assertTrue($this->db->table('test')->exists());
	}

	public function testGetDriver()
	{
		$this->assertEqual($this->db, $this->db->table()->get_driver());
	}

}
// End of table_builder.php