<?php

/**
 * SELECT query builder
 *
 * @method SelectQuery  select(string $column) add one or more columns in SELECT to query
 * @method SelectQuery  leftJoin(string $statement) add LEFT JOIN to query
 *                        ($statement can be 'table' name only or 'table:' means back reference)
 * @method SelectQuery  innerJoin(string $statement) add INNER JOIN to query
 *                        ($statement can be 'table' name only or 'table:' means back reference)
 * @method SelectQuery  groupBy(string $column) add GROUP BY to query
 * @method SelectQuery  having(string $column) add HAVING query
 * @method SelectQuery  orderBy(string $column) add ORDER BY to query
 * @method SelectQuery  limit(int $limit) add LIMIT to query
 * @method SelectQuery  offset(int $offset) add OFFSET to query
 */
class SelectQuery extends CommonQuery {

	private $fromTable, $fromAlias;

	function __construct(FluentPDO $fpdo, $from) {
		$clauses = array(
			'SELECT' => ', ',
			'FROM' => null,
			'JOIN' => array($this, 'getClauseJoin'),
			'WHERE' => ' AND ',
			'GROUP BY' => ',',
			'HAVING' => ' AND ',
			'ORDER BY' => ', ',
			'LIMIT' => null,
			'OFFSET' => null,
			"\n--" => "\n--",
		);
		parent::__construct($fpdo, $clauses);

		# initialize statements
		$fromParts = explode(' ', $from);
		$this->fromTable = reset($fromParts);
		$this->fromAlias = end($fromParts);

		$this->statements['FROM'] = $from;
		$this->statements['SELECT'][] = $this->fromAlias . '.*';
		$this->joins[] = $this->fromAlias;
	}

	/** Return table name from FROM clause
	 * @internal
	 */
	public function getFromTable() {
		return $this->fromTable;
	}

	/** Return table alias from FROM clause
	 * @internal
	 */
	public function getFromAlias() {
		return $this->fromAlias;
	}

	/** Returns a single column
	 * @param int $columnNumber
	 * @return string
	 */
	public function fetchColumn($columnNumber = 0) {
		if ($s = $this->execute()) {
			return $s->fetchColumn($columnNumber);
		}
		return false;
	}

	/** Fetch first row or column
	 * @param string $column column name or empty string for the whole row
	 * @return mixed string, array or false if there is no row
	 */
	public function fetch($column = '') {
		$return = $this->execute();
		if ($return === false) {
			return false;
		}
		$return = $return->fetch();
		if ($return && $column != '') {
			if (is_object($return)) {
				return $return->{$column};
			} else {
				return $return[$column];
			}
		}
		return $return;
	}

	/**
	 * Fetch pairs
	 * @param $key
	 * @param $value
	 * @param $object
	 * @return array of fetched rows as pairs
	 */
	public function fetchPairs($key, $value, $object = false) {
		if ($s = $this->select(null)->select("$key, $value")->asObject($object)->execute()) {
			return $s->fetchAll(PDO::FETCH_KEY_PAIR);
		}
		return false;
	}

	/** Fetch all row
	 * @param string $index  specify index column
	 * @param string $selectOnly  select columns which could be fetched
	 * @return array of fetched rows
	 */
	public function fetchAll($index = '', $selectOnly = '') {
		if ($selectOnly) {
			$this->select(null)->select($index . ', ' . $selectOnly);
		}
		if ($index) {
			$data = array();
			foreach ($this as $row) {
				if (is_object($row)) {
					$data[$row->{$index}] = $row;
				} else {
					$data[$row[$index]] = $row;
				}
			}
			return $data;
		} else {
			if ($s = $this->execute()) {
				return $s->fetchAll();
			}
			return false;
		}
	}

}

