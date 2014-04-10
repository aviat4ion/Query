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
class FirebirdTableTest extends TableBuilderTest {

	public function setUp()
	{
		$dbpath = QTEST_DIR.QDS.'db_files'.QDS.'FB_TEST_DB.FDB';

		if ( ! function_exists('\\fbird_connect'))
		{
			$this->markTestSkipped('Firebird extension does not exist');
		}

		// test the db driver directly
		$this->db = new \Query\Driver\Firebird('localhost:'.$dbpath);
		$this->db->table_prefix = 'create_';
		$this->tables = $this->db->get_tables();
	}

}
// End of FirebirdTableTest.php