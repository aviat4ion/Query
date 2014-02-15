<?php
/**
 * Query
 *
 * Free Query Builder / Database Abstraction Layer
 *
 * @package		Query
 * @author		Timothy J. Warren
 * @copyright	Copyright (c) 2012 - 2013
 * @link 		https://github.com/aviat4ion/Query
 * @license		http://philsturgeon.co.uk/code/dbad-license
 */

// --------------------------------------------------------------------------

/**
 * Unit test bootstrap - Using php simpletest
 */
define('QTEST_DIR', dirname(__FILE__));
define('QBASE_DIR', str_replace(basename(QTEST_DIR), '', QTEST_DIR));
define('QDS', DIRECTORY_SEPARATOR);

// Include simpletest
// it has to be in the tests folder
//require_once('simpletest/autorun.php');



