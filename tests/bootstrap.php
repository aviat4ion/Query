<?php declare(strict_types=1);
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

/**
 * Unit test bootstrap - Using phpunit
 */
define('QTEST_DIR', realpath(__DIR__));
define('QBASE_DIR', realpath(QTEST_DIR . '/../') . '/');
define('QDS', DIRECTORY_SEPARATOR);

function get_json_config()
{
	$files = [
		__DIR__ . '/settings.json',
		__DIR__ . '/settings.json.dist',
	];

	foreach ($files as $file)
	{
		if (is_file($file))
		{
			return json_decode(file_get_contents($file));
		}
	}

	return FALSE;
}

$path = QTEST_DIR . QDS . 'db_files' . QDS . 'test_sqlite.db';
@unlink($path);

require_once __DIR__ . '/BaseTestCase.php';

// End of bootstrap.php
