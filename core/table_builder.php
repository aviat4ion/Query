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
use Query\Driver\Driver_Interface;

// --------------------------------------------------------------------------

/**
 * Abstract class defining database / table creation methods
 *
 * @package Query
 * @subpackage Table_Builder
 */
class Table_Builder {

	/**
	 * The name of the current table
	 * @var string
	 */
	protected $name = '';

	/**
	 * Driver for the current db
	 * @var Driver_Interface
	 */
	private $driver = NULL;

	/**
	 * Options for the current table
	 * @var array
	 */
	private $table_options = array();

	/**
	 * Columns to be added/updated for the current table
	 * @var array
	 */
	private $columns = array();

	/**
	 * Indexes to be added/updated for the current table
	 * @var array
	 */
	private $indexes = array();

	/**
	 * Foreign keys to be added/updated for the current table
	 * @var array
	 */
	private $foreign_keys = array();

	// --------------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @param string $name
	 * @param array $options
	 * @param Driver_Interface $driver
	 * @return Table_Builder
	 */
	public function __construct($name = '', $options = array(), Driver_Interface $driver = NULL)
	{
		$this->table_options = array_merge($this->table_options, $options);

		$this->set_driver($driver);

		if ($name !== '')
		{
			$this->name = (isset($this->driver)) ? $this->driver->prefix_table($name) : $name;
		}

		return $this;
	}

	// --------------------------------------------------------------------------

	/**
	 * Alias to constructor
	 *
	 * @param string $name
	 * @param array $options
	 * @param Driver_Interface $driver
	 * @return Table_Builder
	 */
	public function __invoke($name = '', $options = array(), Driver_Interface $driver = NULL)
	{
		return $this->__construct($name, $options, $driver);
	}

	// --------------------------------------------------------------------------

	/**
	 * Set the reference to the current database driver
	 *
	 * @param Driver_Interface $driver
	 * @return Table_Builder
	 */
	public function set_driver(Driver_Interface $driver = NULL)
	{
		if ( ! is_null($driver))
		{
			$this->driver = $driver;
		}
		return $this;
	}

	// --------------------------------------------------------------------------

	/**
	 * Get the current DB Driver
	 *
	 * @return Driver_Interface
	 */
	public function get_driver()
	{
		return $this->driver;
	}

	// --------------------------------------------------------------------------
	// ! Column Methods
	// --------------------------------------------------------------------------

	/**
	 * Add a column to the current table
	 *
	 * @param string $column_name
	 * @param string $type
	 * @param array $options
	 * @return Table_Builder
	 */
	public function add_column($column_name, $type = NULL, $options = array())
	{
		$col = new Table_Column($column_name, $type, $options);
		$this->columns[] = $col;

		return $this;
	}

	// --------------------------------------------------------------------------

	/**
	 * Remove the specified column name from the current table
	 *
	 * @param string $column_name
	 * @return \Query\Table\Table_Builder
	 */
	public function remove_column($column_name)
	{
		return $this;
	}

	// --------------------------------------------------------------------------

	/**
	 * Rename the specified column on the current table
	 *
	 * @param string $old_name
	 * @param string $new_name
	 * @return \Query\Table\Table_Builder
	 */
	public function rename_column($old_name, $new_name)
	{
		return $this;
	}

	// --------------------------------------------------------------------------

	/**
	 * Change the specified column on the current table
	 *
	 * @param string $column_name
	 * @param string $new_column_type
	 * @param array $options
	 * @return \Query\Table\Table_Builder
	 */
	public function change_column($column_name, $new_column_type, $options = array())
	{
		return $this;
	}

	// --------------------------------------------------------------------------

	/**
	 * Determine whether the column currently exists on the current table
	 *
	 * @param string $column_name
	 * @param array $options
	 * @return bool
	 */
	public function has_column($column_name, $options = array())
	{
		// @TODO: implement
	}

	// --------------------------------------------------------------------------
	// ! Index Methods
	// --------------------------------------------------------------------------

	/**
	 * Add an index to the current table
	 *
	 * @param array $columns
	 * @param array $options
	 * @return \Query\Table\Table_Builder
	 */
	public function add_index($columns, $options = array())
	{
		$col = new Table_Index($columns, $options);
		$this->indexes[] = $col;

		return $this;
	}

	// --------------------------------------------------------------------------

	/**
	 * Remove an index from the current table
	 * @param array $columns
	 * @param array $options
	 * @return \Query\Table\Table_Builder
	 */
	public function remove_index($columns, $options = array())
	{
		// @TODO: implement
		return $this;
	}

	// --------------------------------------------------------------------------

	/**
	 * Remove an index by its name from the current table
	 *
	 * @param string $name
	 * @return \Query\Table\Table_Builder
	 */
	public function remove_index_by_name($name)
	{
		// @TODO: implement
		return $this;
	}

	// --------------------------------------------------------------------------

	/**
	 * Check if the current table has an index on the specified columns
	 *
	 * @param array $columns
	 * @param array $options
	 * @return bool
	 */
	public function has_index($columns, $options = array())
	{
		// @TODO: implement
	}

	// --------------------------------------------------------------------------
	// ! Foreign Key Methods
	// --------------------------------------------------------------------------

	/**
	 * Add a foreign key to the current table
	 *
	 * @param array $columns
	 * @param string $referenced_table
	 * @param array $referenced_columns
	 * @param array $options
	 * @return \Query\Table\Table_Builder
	 */
	public function add_foreign_key($columns, $referenced_table, $referenced_columns = array('id'), $options = array())
	{
		$key = new Table_Foreign_Key($columns, $referenced_table, $referenced_columns, $options);
		$this->foreign_keys[] = $key;

		return $this;
	}

	// --------------------------------------------------------------------------

	/**
	 * Drop the foreign key from the current table
	 *
	 * @param array $columns
	 * @param string $constraint
	 * @return \Query\Table\Table_Builder
	 */
	public function drop_foreign_key($columns, $constraint = NULL)
	{
		// @TODO: implement
		return $this;
	}

	// --------------------------------------------------------------------------

	/**
	 * Determine whether the current table has the specified foreign key
	 *
	 * @param array $columns
	 * @param string $constraint
	 * @return bool
	 */
	public function has_foreign_key($columns, $constraint = NULL)
	{
		// @TODO: implement
		$keys = $this->get_driver()->get_fks($this->name);


		foreach($keys as $key)
		{

		}

		return FALSE;
	}

	// --------------------------------------------------------------------------
	// ! Table-wide methods
	// --------------------------------------------------------------------------

	/**
	 * Check whether the current table exists
	 *
	 * @return bool
	 */
	public function exists()
	{
		$tables = $this->driver->get_tables();
		return in_array($this->name, $tables);
	}

	// --------------------------------------------------------------------------

	/**
	 * Drop the current table
	 *
	 * @return void
	 */
	public function drop()
	{
		// @TODO: implement
	}

	// --------------------------------------------------------------------------

	/**
	 * Rename the current table
	 *
	 * @param string $new_table_name
	 * @return void
	 */
	public function rename($new_table_name)
	{
		// @TODO: implement
	}

	// --------------------------------------------------------------------------

	/**
	 * Get the list of columns for the current table
	 *
	 * @return array
	 */
	public function get_columns()
	{
		return $this->driver->get_columns($this->name);
	}


	// --------------------------------------------------------------------------
	// ! Action methods
	// --------------------------------------------------------------------------

	/**
	 * Create the table from the previously set options
	 *
	 * @return void
	 */
	public function create()
	{
		// @TODO: implement
		$this->reset();
	}

	// --------------------------------------------------------------------------

	/**
	 * Update the current table with the changes made
	 *
	 * @return void
	 */
	public function update()
	{
		// @TODO: implement
		$this->reset();
	}

	// --------------------------------------------------------------------------

	/**
	 * Save the changes made to the table
	 *
	 * @return void
	 */
	public function save()
	{
		($this->exists())
			? $this->update()
			: $this->create();
	}

	// --------------------------------------------------------------------------

	/**
	 * Reset the state of the table builder
	 *
	 * @return void
	 */
	public function reset()
	{
		$skip = array(
			'driver' => 'driver'
		);

		foreach($this as $key => $val)
		{
			if ( ! isset($skip[$key]))
			{
				$this->$key = NULL;
			}
		}
	}

}
// End of table_builder.php
