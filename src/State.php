<?php declare(strict_types=1);
/**
 * Query
 *
 * SQL Query Builder / Database Abstraction Layer
 *
 * PHP version 7.1
 *
 * @package     Query
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2012 - 2018 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link        https://git.timshomepage.net/aviat4ion/Query
 */
namespace Query;

/**
 * Query builder state
 */
class State {
	// --------------------------------------------------------------------------
	// ! SQL Clause Strings
	// --------------------------------------------------------------------------

	/**
	 * Compiled 'select' clause
	 * @var string
	 */
	protected $selectString = '';

	/**
	 * Compiled 'from' clause
	 * @var string
	 */
	protected $fromString = '';

	/**
	 * Compiled arguments for insert / update
	 * @var string
	 */
	protected $setString = '';

	/**
	 * Order by clause
	 * @var string
	 */
	protected $orderString = '';

	/**
	 * Group by clause
	 * @var string
	 */
	protected $groupString = '';

	// --------------------------------------------------------------------------
	// ! SQL Clause Arrays
	// --------------------------------------------------------------------------

	/**
	 * Keys for insert/update statement
	 * @var array
	 */
	protected $setArrayKeys = [];

	/**
	 * Key/val pairs for order by clause
	 * @var array
	 */
	protected $orderArray = [];

	/**
	 * Key/val pairs for group by clause
	 * @var array
	 */
	protected $groupArray = [];

	// --------------------------------------------------------------------------
	// ! Other Class vars
	// --------------------------------------------------------------------------

	/**
	 * Values to apply to prepared statements
	 * @var array
	 */
	protected $values = [];

	/**
	 * Values to apply to where clauses in prepared statements
	 * @var array
	 */
	protected $whereValues = [];

	/**
	 * Value for limit string
	 * @var integer
	 */
	protected $limit;

	/**
	 * Value for offset in limit string
	 * @var string|false
	 */
	protected $offset = FALSE;

	/**
	 * Query component order mapping
	 * for complex select queries
	 *
	 * Format:
	 * [
	 *		'type' => 'where',
	 *		'conjunction' => ' AND ',
	 *		'string' => 'k=?'
	 * ]
	 *
	 * @var array
	 */
	protected $queryMap = [];

	/**
	 * Map for having clause
	 * @var array
	 */
	protected $havingMap = [];

	/**
	 * @param string $str
	 * @return State
	 */
	public function setSelectString(string $str): self
	{
		$this->selectString = $str;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSelectString(): string
	{
		return $this->selectString;
	}

	/**
	 * @param string $str
	 * @return State
	 */
	public function appendSelectString(string $str): self
	{
		$this->selectString .= $str;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getFromString(): string
	{
		return $this->fromString;
	}

	/**
	 * @param string $fromString
	 * @return State
	 */
	public function setFromString(string $fromString): self
	{
		$this->fromString = $fromString;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSetString(): string
	{
		return $this->setString;
	}

	/**
	 * @param string $setString
	 * @return State
	 */
	public function setSetString(string $setString): self
	{
		$this->setString = $setString;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getOrderString(): string
	{
		return $this->orderString;
	}

	/**
	 * @param string $orderString
	 * @return State
	 */
	public function setOrderString(string $orderString): self
	{
		$this->orderString = $orderString;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getGroupString(): string
	{
		return $this->groupString;
	}

	/**
	 * @param string $groupString
	 * @return State
	 */
	public function setGroupString(string $groupString): self
	{
		$this->groupString = $groupString;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getSetArrayKeys(): array
	{
		return $this->setArrayKeys;
	}

	/**
	 * @param array $setArrayKeys
	 * @return State
	 */
	public function appendSetArrayKeys(array $setArrayKeys): self
	{
		$this->setArrayKeys = array_merge($this->setArrayKeys, $setArrayKeys);
		return $this;
	}

	/**
	 * @param array $setArrayKeys
	 * @return State
	 */
	public function setSetArrayKeys(array $setArrayKeys): self
	{
		$this->setArrayKeys = $setArrayKeys;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getOrderArray(): array
	{
		return $this->orderArray;
	}

	/**
	 * @param string $key
	 * @param mixed $orderArray
	 * @return State
	 */
	public function setOrderArray(string $key, $orderArray): self
	{
		$this->orderArray[$key] = $orderArray;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getGroupArray(): array
	{
		return $this->groupArray;
	}

	/**
	 * @param array $groupArray
	 * @return State
	 */
	public function setGroupArray(array $groupArray): self
	{
		$this->groupArray = $groupArray;
		return $this;
	}

	/**
	 * @param string $groupArray
	 * @return State
	 */
	public function appendGroupArray(string $groupArray): self
	{
		$this->groupArray[] = $groupArray;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getValues(): array
	{
		return $this->values;
	}

	/**
	 * @param array $values
	 * @return State
	 */
	public function appendValues(array $values): self
	{
		$this->values = array_merge($this->values, $values);
		return $this;
	}

	/**
	 * @return array
	 */
	public function getWhereValues(): array
	{
		return $this->whereValues;
	}

	/**
	 * @param mixed $val
	 * @return State
	 */
	public function appendWhereValues($val): self
	{
		if (\is_array($val))
		{
			foreach($val as $v)
			{
				$this->whereValues[] = $v;
			}

			return $this;
		}

		$this->whereValues[] = $val;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getLimit(): ?int
	{
		return $this->limit;
	}

	/**
	 * @param int $limit
	 * @return State
	 */
	public function setLimit(int $limit): self
	{
		$this->limit = $limit;
		return $this;
	}

	/**
	 * @return string|false
	 */
	public function getOffset()
	{
		return $this->offset;
	}

	/**
	 * @param string|false $offset
	 * @return State
	 */
	public function setOffset($offset): self
	{
		$this->offset = $offset;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getQueryMap(): array
	{
		return $this->queryMap;
	}

	/**
	 * Add an additional set of mapping pairs to a internal map
	 *
	 * @param string $conjunction
	 * @param string $string
	 * @param string $type
	 * @return State
	 */
	public function appendMap(string $conjunction = '', string $string = '', string $type = ''): self
	{
		$this->queryMap[] = [
			'type' => $type,
			'conjunction' => $conjunction,
			'string' => $string
		];
		return $this;
	}

	/**
	 * @return array
	 */
	public function getHavingMap(): array
	{
		return $this->havingMap;
	}

	/**
	 * @param array $item
	 * @return State
	 */
	public function appendHavingMap(array $item): self
	{
		$this->havingMap[] = $item;
		return $this;
	}
}
