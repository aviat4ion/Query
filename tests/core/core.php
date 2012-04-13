<?php
/**
 * OpenSQLManager
 *
 * Free Database manager for Open Source Databases
 *
 * @author 		Timothy J. Warren
 * @copyright	Copyright (c) 2012
 * @link 		https://github.com/aviat4ion/OpenSQLManager
 * @license 	http://philsturgeon.co.uk/code/dbad-license 
 */

// --------------------------------------------------------------------------
/**
 * CoreTest class - Compatibility and core functionality tests
 * 
 * @extends UnitTestCase
 */
class CoreTest extends UnitTestCase {
	
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
	
	/**
	 * TestPHPVersion function.
	 * 
	 * @access public
	 * @return void
	 */
	function TestPHPVersion()
	{
		$this->assertTrue(version_compare(PHP_VERSION, "5.2", "ge"));
	}
	
	/**
	 * TestHasPDO function.
	 * 
	 * @access public
	 * @return void
	 */
	function TestHasPDO()
	{
		// PDO class exists
		$this->assertTrue(class_exists('PDO'));
		
		
		// Make sure at least one of the supported drivers is enabled
		$supported = array(
			'mysql',
			'pgsql',
			'odbc',
			'sqlite',
		);
		
		$drivers = pdo_drivers();
		
		$num_supported = count(array_intersect($drivers, $supported));
		
		$this->assertTrue($num_supported > 0);
	}
}