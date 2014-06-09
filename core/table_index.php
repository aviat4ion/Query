<?php
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

// --------------------------------------------------------------------------

namespace Query\Table;

/**
 * Class representing indicies when creating a table
 */
class Table_Index extends Abstract_Table {

	/**
	 * Valid options for a table index
	 * @var array
	 */
	protected $valid_options = array(
		'type',
		'unique',
		'name'
	);

	/**
	 * Return the string representation of the current index
	 */
	public function __toString()
	{
		// @TODO: implement
	}

}
// End of table_index.php
