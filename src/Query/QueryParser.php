<?php
/**
 * Query
 *
 * SQL Query Builder / Database Abstraction Layer
 *
 * PHP version 5.4
 *
 * @package     Query
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2012 - 2015 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link        https://git.timshomepage.net/aviat4ion/Query
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
	private $match_patterns = [
		'function' => '([a-zA-Z0-9_]+\((.*?)\))',
		'identifier' => '([a-zA-Z0-9_-]+\.?)+',
		'operator' => '=|AND|&&?|~|\|\|?|\^|/|>=?|<=?|-|%|OR|\+|NOT|\!=?|<>|XOR'
	];

	/**
	 * Regex matches
	 *
	 * @var array
	 */
	public $matches = [
		'functions' => [],
		'identifiers' => [],
		'operators' => [],
		'combined' => [],
	];

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
		$this->matches = array_map([$this, 'filter_array'], $this->matches);

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
		$new_array = [];

		foreach($array as $row)
		{
			$new_array[] =  (is_array($row)) ? $row[0] : $row;
		}

		return $new_array;
	}

}

// End of QueryParser.php