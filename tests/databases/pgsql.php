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

/**
 * PgTest class.
 * 
 * @extends UnitTestCase
 */
class PgTest extends UnitTestCase {

	/**
	 * __construct function.
	 * 
	 * @access public
	 * @return void
	 */
	function __construct()
	{
		parent::__construct();
	}
	
	function TestExists()
	{
		$this->assertTrue(in_array('pgsql', pdo_drivers()));
	}
}