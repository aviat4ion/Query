<?php declare(strict_types=1);
/**
 * Query
 *
 * SQL Query Builder / Database Abstraction Layer
 *
 * PHP version 7.4
 *
 * @package     Query
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2012 - 2020 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link        https://git.timshomepage.net/aviat/Query
 * @version     3.0.0
 */
namespace Query;

use function is_array;

/**
 * Query builder state
 *
 * @method getSelectString(): string
 * @method getFromString(): string
 * @method getSetString(): string
 * @method getOrderString(): string
 * @method getGroupString(): string
 * @method getSetArrayKeys(): array
 * @method getOrderArray(): array
 * @method getGroupArray(): array
 * @method getValues(): array
 * @method getWhereValues(): array
 * @method getLimit(): int|null
 * @method getOffset()
 * @method getQueryMap(): array
 * @method getHavingMap(): array
 *
 * @method setSelectString(string $selectString): self
 * @method setFromString(string $fromString): self
 * @method setSetString(string $setString): self
 * @method setOrderString(string $orderString): self
 * @method setGroupString(string $groupString): self
 * @method setSetArrayKeys(array $arrayKeys): self
 * @method setGroupArray(array $array): self
 * @method setLimit(int $limit): self
 * @method setOffset(?int $offset): self
 */
class State {
	// --------------------------------------------------------------------------
	// ! SQL Clause Strings
	// --------------------------------------------------------------------------
	/**
	 * Compiled 'select' clause
	 */
	protected string $selectString = '';

	/**
	 * Compiled 'from' clause
	 */
	protected string $fromString = '';

	/**
	 * Compiled arguments for insert / update
	 */
	protected string $setString = '';

	/**
	 * Order by clause
	 */
	protected string $orderString = '';

	/**
	 * Group by clause
	 */
	protected string $groupString = '';

	// --------------------------------------------------------------------------
	// ! SQL Clause Arrays
	// --------------------------------------------------------------------------
	/**
	 * Keys for insert/update statement
	 */
	protected array $setArrayKeys = [];

	/**
	 * Key/val pairs for order by clause
	 */
	protected array $orderArray = [];

	/**
	 * Key/val pairs for group by clause
	 */
	protected array $groupArray = [];

	// --------------------------------------------------------------------------
	// ! Other Class vars
	// --------------------------------------------------------------------------
	/**
	 * Values to apply to prepared statements
	 */
	protected array $values = [];

	/**
	 * Values to apply to where clauses in prepared statements
	 */
	protected array $whereValues = [];

	/**
	 * Value for limit string
	 */
	protected ?int $limit = NULL;

	/**
	 * Value for offset in limit string
	 */
	protected ?int $offset = NULL;

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
	 */
	protected array $queryMap = [];

	/**
	 * Map for having clause
	 */
	protected array $havingMap = [];

	public function __call(string $name, array $arguments)
	{
		if (str_starts_with($name, 'get'))
		{
			$maybeProp = lcfirst(substr($name, 3));
			if (isset($this->$maybeProp))
			{
				return $this->$maybeProp;
			}
		}

		if (str_starts_with($name, 'set'))
		{
			$maybeProp = lcfirst(substr($name, 3));
			if (isset($this->$maybeProp))
			{
				$this->$maybeProp = $arguments[0];
				return $this;
			}
		}

		return NULL;
	}

	public function appendSelectString(string $str): self
	{
		$this->selectString .= $str;
		return $this;
	}

	public function appendSetArrayKeys(array $setArrayKeys): self
	{
		$this->setArrayKeys = array_merge($this->setArrayKeys, $setArrayKeys);
		return $this;
	}

	/**
	 * @param mixed $orderArray
	 */
	public function setOrderArray(string $key, $orderArray): self
	{
		$this->orderArray[$key] = $orderArray;
		return $this;
	}

	public function appendGroupArray(string $groupArray): self
	{
		$this->groupArray[] = $groupArray;
		return $this;
	}

	public function appendValues(array $values): self
	{
		$this->values = array_merge($this->values, $values);
		return $this;
	}

	/**
	 * @param mixed $val
	 */
	public function appendWhereValues($val): self
	{
		if (is_array($val))
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
	 * Add an additional set of mapping pairs to a internal map
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

	public function appendHavingMap(array $item): self
	{
		$this->havingMap[] = $item;
		return $this;
	}
}
