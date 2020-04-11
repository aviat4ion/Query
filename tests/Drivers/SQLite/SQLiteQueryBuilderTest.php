<?php declare(strict_types=1);
/**
 * Query
 *
 * SQL Query Builder / Database Abstraction Layer
 *
 * PHP version 7.4
 *
 * @package     Query
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2012 - 2020 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link        https://git.timshomepage.net/aviat/Query
 * @version     3.0.0
 */
namespace Query\Tests\Drivers\SQLite;

use PDO;
use Query\Tests\BaseQueryBuilderTest;

/**
 * Class for testing Query Builder with SQLite
 *
 * @requires extension pdo_sqlite
 */
 class SQLiteQueryBuilderTest extends BaseQueryBuilderTest {

	public static function setUpBeforeClass(): void
	{
		// Defined in the SQLiteTest.php file
		self::$db = Query('test_sqlite');
	}

	public function testQueryFunctionAlias(): void
	{
		$db = Query('test_sqlite');

		$this->assertTrue(self::$db === $db, 'Alias passed into query function gives the original object back');
	}

	public function testQueryExplain(): void
	{
		$query = self::$db->select('id, key as k, val')
			->explain()
			->where('id >', 1)
			->where('id <', 900)
			->get('create_test', 2, 1);

		$res = $query->fetchAll(PDO::FETCH_ASSOC);

		$expectedPossibilities = [];

		$expectedPossibilities[] = [
			[
				'order' => '0',
				'from' => '0',
				'detail' => 'TABLE create_test USING PRIMARY KEY',
			]
		];

		$expectedPossibilities[] = [
			[
				'selectid' => '0',
				'order' => '0',
				'from' => '0',
				'detail' => 'SEARCH TABLE create_test USING INTEGER PRIMARY KEY (rowid>? AND rowid<?) (~60000 rows)',
			],
		];

		$expectedPossibilities[] = [
			[
				'selectid' => '0',
				'order' => '0',
				'from' => '0',
				'detail' => 'SEARCH TABLE create_test USING INTEGER PRIMARY KEY (rowid>? AND rowid<?)',
			],
		];

		$expectedPossibilities[] = [
			[
				'selectid' => '0',
				'order' => '0',
				'from' => '0',
				'detail' => 'SEARCH TABLE create_test USING INTEGER PRIMARY KEY (rowid>? AND rowid<?) (~62500 rows)',
			],
		];

		$expectedPossibilities[] = [
			[
				'id' => '6',
				'parent' => '0',
				'notused' => '0',
				'detail' => 'SEARCH TABLE create_test USING INTEGER PRIMARY KEY (rowid>? AND rowid<?)',
			],
		];

		$passed = FALSE;

		// Check for a matching possibility
		foreach($expectedPossibilities as $ep)
		{
			if ($res === $ep)
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

	 public function testInsertReturning(): void
	 {
		 $this->markTestSkipped();
	 }

	 public function testUpdateReturning(): void
	 {
		 $this->markTestSkipped();
	 }
}