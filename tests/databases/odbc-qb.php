<?php
/**
 * Query
 *
 * Free Query Builder / Database Abstraction Layer
 *
 * @author 		Timothy J. Warren
 * @copyright	Copyright (c) 2012
 * @link 		https://github.com/aviat4ion/Query
 * @license 	http://philsturgeon.co.uk/code/dbad-license 
 */

// --------------------------------------------------------------------------

class ODBCQBTest extends UnitTestCase {

	function __construct()
	{
	
	}
	
	function TestExists()
	{
		$this->assertTrue(in_array('odbc', pdo_drivers()));
	}
}