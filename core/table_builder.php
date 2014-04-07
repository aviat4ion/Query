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
	public function __construct($name, $options = array(), \Query\Driver\Driver_Interface $driver = NULL)
	{
		$this->name = $name;

		if ( ! empty($options))
		{
			$this->table_options = array_merge($this->table_options, $options);
		}

		if ( ! is_null($driver))
		{
			$this->driver = $driver;
		}

		return $this;
	}

	// --------------------------------------------------------------------------

	/**
	 * Alias to constructor
	 *
	 * @param string $name
	 * @param array $options
	 * @param \Query\Driver_Interface $driver
	 */
	public function __invoke($name, $options = array(), \Query\Driver\Driver_Interface $driver = NULL)
	{
		$this->__construct($name, $options, $driver);
	}

	// --------------------------------------------------------------------------

	/**
	 * Set the reference to the current database driver
	 *
	 * @param \Query\Driver_Interface $driver
	 * @return \Query\Table_Builder
	 */
	public function set_driver(\Query\Driver_Interface $driver)
	{
		$this->driver = $driver;
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
	 */
	public function add_column($column_name, $type = NULL, $options = array())
	{
		$col = new Table_Column($column_name, $type, $options);
		$this->columns[] = $col;
	}

	// --------------------------------------------------------------------------

	public function remove_column($column_name)
	{

	}

	// --------------------------------------------------------------------------

	public function rename_column($old_name, $new_name)
	{

	}

	// --------------------------------------------------------------------------

	public function change_column($column_name, $new_column_type, $options = array())
	{

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

	}

	// --------------------------------------------------------------------------

	public function remove_index($columns, $options = array())
	{

	}

	// --------------------------------------------------------------------------

	public function remove_index_by_name($name)
	{

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

	}

	// --------------------------------------------------------------------------

	public function update()
	{

	}

	// --------------------------------------------------------------------------

	public function save()
	{
		($this->exists())
			? $this->update()
			: $this->create();

		$this->reset();
	}

	// --------------------------------------------------------------------------

	public function reset()
	{

	}

}
// End of table_bulider.php
