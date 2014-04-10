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
	 * @param [string] $name
	 * @param [array] $options
	 * @param [Driver_Interface] $driver
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
	 * @param [string] $name
	 * @param [array] $options
	 * @param [\Query\Driver\Driver_Interface] $driver
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
	 * @param \Query\Driver\Driver_Interface $driver
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
	 * @return \Query\Driver_Interface
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

	public function remove_column($column_name)
	{
		return $this;
	}

	// --------------------------------------------------------------------------

	public function rename_column($old_name, $new_name)
	{
		return $this;
	}

	// --------------------------------------------------------------------------

	public function change_column($column_name, $new_column_type, $options = array())
	{
		return $this;
	}

	// --------------------------------------------------------------------------

	public function has_column($column_name, $options = array())
	{

	}

	// --------------------------------------------------------------------------
	// ! Index Methods
	// --------------------------------------------------------------------------

	public function add_index($columns, $options = array())
	{
		$col = new Table_Index($columns, $options);
		$this->indexes[] = $col;

		return $this;
	}

	// --------------------------------------------------------------------------

	public function remove_index($columns, $options = array())
	{
		return $this;
	}

	// --------------------------------------------------------------------------

	public function remove_index_by_name($name)
	{
		return $this;
	}

	// --------------------------------------------------------------------------

	public function has_index($columns, $options = array())
	{

	}

	// --------------------------------------------------------------------------
	// ! Foreign Key Methods
	// --------------------------------------------------------------------------

	public function add_foreign_key($columns, $referenced_table, $referenced_columns = array('id'), $options = array())
	{
		$key = new Table_Foreign_Key($columns, $referenced_table, $referenced_columns, $options);
		$this->foreign_keys[] = $key;

		return $this;
	}

	// --------------------------------------------------------------------------

	public function drop_foreign_key($columns, $constraint = NULL)
	{

	}

	// --------------------------------------------------------------------------

	public function has_foreign_key($columns, $constraint = NULL)
	{

	}

	// --------------------------------------------------------------------------
	// ! Table-wide methods
	// --------------------------------------------------------------------------

	public function exists()
	{
		$tables = $this->driver->get_tables();
		return in_array($this->name, $tables);
	}

	// --------------------------------------------------------------------------

	public function drop()
	{

	}

	// --------------------------------------------------------------------------

	public function rename($new_table_name)
	{

	}

	// --------------------------------------------------------------------------

	public function get_columns()
	{

	}


	// --------------------------------------------------------------------------
	// ! Action methods
	// --------------------------------------------------------------------------

	public function create()
	{
		$this->reset();
	}

	// --------------------------------------------------------------------------

	public function update()
	{
		$this->reset();
	}

	// --------------------------------------------------------------------------

	public function save()
	{
		($this->exists())
			? $this->update()
			: $this->create();
	}

	// --------------------------------------------------------------------------

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
// End of table_bulider.php
