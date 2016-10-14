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
 * Class for testing Query Builder with SQLite
 *
 * @requires extension pdo_sqlite
 */
 class SQLiteQBTest extends QBTest {

	public static function setUpBeforeClass()
	{
		// Defined in the SQLiteTest.php file
		self::$db = Query('test_sqlite');
	}

 	// --------------------------------------------------------------------------

	public function testQueryFunctionAlias()
	{
		$db = Query('test_sqlite');

		$this->assertTrue(self::$db === $db, "Alias passed into query function gives the original object back");
	}

	// --------------------------------------------------------------------------

	public function testQueryExplain()
	{
		$query = self::$db->select('id, key as k, val')
			->explain()
			->where('id >', 1)
			->where('id <', 900)
			->get('create_test', 2, 1);

		$res = $query->fetchAll(PDO::FETCH_ASSOC);

		$expectedPossibilities = array();

		$expectedPossibilities[] = array(
			array(
				'order' => '0',
				'from' => '0',
				'detail' => 'TABLE create_test USING PRIMARY KEY',
			)
		);

		$expectedPossibilities[] = array (
			array (
				'selectid' => '0',
				'order' => '0',
				'from' => '0',
				'detail' => 'SEARCH TABLE create_test USING INTEGER PRIMARY KEY (rowid>? AND rowid<?) (~60000 rows)',
			),
		);

		$expectedPossibilities[] = array (
			array (
				'selectid' => '0',
				'order' => '0',
				'from' => '0',
				'detail' => 'SEARCH TABLE create_test USING INTEGER PRIMARY KEY (rowid>? AND rowid<?)',
			),
		);

		$expectedPossibilities[] = array (
			array (
				'selectid' => '0',
				'order' => '0',
				'from' => '0',
				'detail' => 'SEARCH TABLE create_test USING INTEGER PRIMARY KEY (rowid>? AND rowid<?) (~62500 rows)',
			),
		);

		$passed = FALSE;

		// Check for a matching possibility
		foreach($expectedPossibilities as $ep)
		{
			if ($res == $ep)
			{
				$this->assertTrue(TRUE);
				$passed = TRUE;
			}
		}

		// Well, apparently not an expected possibility
		if ( ! $passed)
		{
			var_export($res);
			$this->assertTrue(FALSE);
		}
	}
}