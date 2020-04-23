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
namespace Query\Tests\Drivers\MySQL;

use PDO;
use Query\Tests\BaseQueryBuilderTest;

/**
 * @requires extension pdo_mysql
 */
class MySQLQueryBuilderTest extends BaseQueryBuilderTest {

	public static function setUpBeforeClass(): void
	{
		$params = get_json_config();
		if ($var = getenv('TRAVIS')) // Travis CI Connection Info
		{
			$params = [
				'host' => '127.0.0.1',
				'port' => '3306',
				'database' => 'test',
				'prefix' => 'create_',
				'user' => 'root',
				'pass' => NULL,
				'type' => 'mysql'
			];
		}
		// Attempt to connect, if there is a test config file
		else if ($params !== FALSE)
		{
			$params = $params->mysql;
			$params->type = 'MySQL';
			$params->options = [];
			$params->options[PDO::ATTR_PERSISTENT]  = TRUE;
		}

		self::$db = Query($params);
	}

	public function testExists(): void
	{
		$this->assertTrue(\in_array('mysql', PDO::getAvailableDrivers(), TRUE));
	}

	public function testQueryExplain(): void
	{
		$query = self::$db->select('id, key as k, val')
			->explain()
			->where('id >', 1)
			->where('id <', 900)
			->get('test', 2, 1);

		$res = $query->fetchAll(PDO::FETCH_ASSOC);

		// The exact results are version dependent
		// The important thing is that there is an array
		// of results returned
		$this->assertTrue(\is_array($res));
		$this->assertTrue(count(array_keys($res[0])) > 1);
		$this->assertTrue(array_key_exists('table', $res[0]));
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