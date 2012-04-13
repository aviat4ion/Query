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
 * Settings Class Test Class
 */
class SettingsTest extends UnitTestCase {

	function __construct()
	{
		parent::__construct();
		$this->settings =& Settings::get_instance();
		
		// Make sure to delete 'foo' if it exists
		$this->settings->remove_db('foo');
	}

	function TestExists()
	{
		$this->assertIsA($this->settings, 'Settings');
	}
	
	function TestGetEmptyDBs()
	{
		$this->assertTrue(is_object($this->settings->get_dbs()));
	}
	
	function TestGetNull()
	{
		$this->assertFalse(isset($this->settings->foo));
	}
	
	function TestSet()
	{
		$bar = $this->settings->foo = 'bar';
	
		$this->assertEqual('bar', $bar);
	}
	
	function TestGet()
	{
		$this->assertEqual('bar', $this->settings->foo);
	}
	
	function TestSetDBProperty()
	{
		$res = $this->settings->__set('dbs', 2);
		$this->assertFalse($res);
	}
	
	function TestGetEmptyDB()
	{
		$this->assertFalse($this->settings->get_db('foo'));
	}
	
	function TestAddDB()
	{
		$this->settings->add_db('foo', array());
		
		$db = $this->settings->get_db('foo');
		
		$this->assertTrue(isset($db));
	}
}
