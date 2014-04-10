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
class SQLiteTableTest extends TableBuilderTest {

	public function setUp()
	{
		// Set up in the bootstrap to mitigate
		// connection locking issues
		$this->db = Query('test_sqlite');
		$this->db->table_prefix = 'create_';
	}

}
// End of SQLiteTableTest.php