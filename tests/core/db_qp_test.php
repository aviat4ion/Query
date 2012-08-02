<?php
/**
 * Query
 *
 * Free Query Builder / Database Abstraction Layer
 *
 * @package		Query
 * @author		Timothy J. Warren
 * @copyright	Copyright (c) 2012
 * @link 		https://github.com/aviat4ion/Query
 * @license		http://philsturgeon.co.uk/code/dbad-license
 */

// --------------------------------------------------------------------------

class QPTest extends UnitTestCase {

	public function __construct()
	{
		$this->parser = new Query_Parser();
	}

	public function TestGeneric()
	{
		$this->parser->__construct('table1.field1=table2.field2');

		//echo '<pre>'.print_r($this->parser->matches, TRUE).'</pre>';
	}

	public function TestFunction()
	{
		$this->parser->__construct('table1.field1 > SUM(3+5)');

		//echo '<pre>'.print_r($this->parser->matches, TRUE).'</pre>';
	}

}