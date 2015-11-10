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

namespace Query;

/**
 * Utility Class to parse sql clauses for properly escaping identifiers
 *
 * @package Query
 * @subpackage Query_Builder
 */
class QueryParser {

	/**
	 * DB Driver
	 *
	 * @var DriverInterface
	 */
	private $db;

	/**
	 * Regex patterns for various syntax components
	 *
	 * @var array
	 */
	private $match_patterns = array(
		'function' => '([a-zA-Z0-9_]+\((.*?)\))',
		'identifier' => '([a-zA-Z0-9_-]+\.?)+',
		'operator' => '=|AND|&&?|~|\|\|?|\^|/|>=?|<=?|-|%|OR|\+|NOT|\!=?|<>|XOR'
	);

	/**
	 * Regex matches
	 *
	 * @var array
	 */
	public $matches = array(
		'functions' => array(),
		'identifiers' => array(),
		'operators' => array(),
		'combined' => array(),
	);

	/**
	 * Constructor/entry point into parser
	 *
	 * @param Driver\DriverInterface $db
	 */
	public function __construct(DriverInterface $db)
	{
		$this->db = $db;
	}

	// --------------------------------------------------------------------------

	/**
	 * Parser method for setting the parse string
	 *
	 * @param string $sql
	 * @return array
	 */
	public function parse_join($sql)
	{
		// Get sql clause components
		preg_match_all('`'.$this->match_patterns['function'].'`', $sql, $this->matches['functions'], PREG_SET_ORDER);
		preg_match_all('`'.$this->match_patterns['identifier'].'`', $sql, $this->matches['identifiers'], PREG_SET_ORDER);
		preg_match_all('`'.$this->match_patterns['operator'].'`', $sql, $this->matches['operators'], PREG_SET_ORDER);

		// Get everything at once for ordering
		$full_pattern = '`'.$this->match_patterns['function'].'+|'.$this->match_patterns['identifier'].'|('.$this->match_patterns['operator'].')+`i';
		preg_match_all($full_pattern, $sql, $this->matches['combined'], PREG_SET_ORDER);

		// Go through the matches, and get the most relevant matches
		$this->matches = array_map(array($this, 'filter_array'), $this->matches);

		return $this->matches;
	}

	// --------------------------------------------------------------------------

	/**
	 * Compiles a join condition after parsing
	 *
	 * @param string $condition
	 * @return string
	 */
	public function compile_join($condition)
	{
		$parts = $this->parse_join($condition);
		$count = count($parts['identifiers']);

		// Go through and quote the identifiers
		for($i=0; $i <= $count; $i++)
		{
			if (in_array($parts['combined'][$i], $parts['identifiers']) && ! is_numeric($parts['combined'][$i]))
			{
				$parts['combined'][$i] = $this->db->quote_ident($parts['combined'][$i]);
			}
		}

		return implode('', $parts['combined']);
	}

	// --------------------------------------------------------------------------

	/**
	 * Returns a more useful match array
	 *
	 * @param array $array
	 * @return array
	 */
	protected function filter_array($array)
	{
		$new_array = array();

		foreach($array as $row)
		{
			$new_array[] =  (is_array($row)) ? $row[0] : $row;
		}

		return $new_array;
	}

}

// End of query_parser.php