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

/**
 * Utility Class to parse sql clauses for properly escaping identifiers
 *
 * @package Query
 * @subpackage Query
 */
class Query_Parser {

	/**
	 * Regex patterns for various syntax components
	 *
	 * @var array
	 */
	private $match_patterns = array(
		'function' => '`([a-zA-Z0-9_]+\((.*?)\))`',
		'identifier' => '`([a-zA-Z0-9"_-]+\.?)+`',
		'operator' => '`=|AND|&&?|~|\|\|?|\^|/|>=?|<=?|-|%|OR|\+|NOT|\!=?|<>|XOR`'
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
	);

	/**
	 * Constructor/entry point into parser
	 *
	 * @param string
	 */
	public function __construct($sql = '')
	{
		preg_match_all($this->match_patterns['function'], $sql, $this->matches['functions'], PREG_SET_ORDER);
		preg_match_all($this->match_patterns['identifier'], $sql, $this->matches['identifiers'], PREG_SET_ORDER);
		preg_match_all($this->match_patterns['operator'], $sql, $this->matches['operators'], PREG_SET_ORDER);
	}

}

// End of query_parser.php