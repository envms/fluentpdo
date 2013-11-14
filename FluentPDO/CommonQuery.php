<?php

/** CommonQuery add JOIN and WHERE clauses for (SELECT, UPDATE, DELETE)
 */
abstract class CommonQuery extends BaseQuery {

	/** @var array of used tables (also include table from clause FROM) */
	protected $joins = array();

	/** @var boolean disable adding undefined joins to query? */
	protected $isSmartJoinEnabled = true;

	public function enableSmartJoin() {
		$this->isSmartJoinEnabled = true;
		return $this;
	}

	public function disableSmartJoin() {
		$this->isSmartJoinEnabled = false;
		return $this;
	}

	public function isSmartJoinEnabled() {
		return $this->isSmartJoinEnabled;
	}

	/** Add where condition, more calls appends with AND
	 * @param string $condition  possibly containing ? or :name (PDO syntax)
	 * @param mixed $parameters  array or a scalar value
	 * @return SelectQuery
	 */
	public function where($condition, $parameters = array()) {
		if ($condition === null) {
			return $this->resetClause('WHERE');
		}
		if (!$condition) {
			return $this;
		}
		if (is_array($condition)) { // where(array("column1" => 1, "column2 > ?" => 2))
			foreach ($condition as $key => $val) {
				$this->where($key, $val);
			}
			return $this;
		}
		$args = func_get_args();
		if (count($args) == 1) {
			return $this->addStatement('WHERE', $condition);
		}
		if (count($args) == 2 && preg_match('~^[a-z_:][a-z0-9_.:]*$~i', $condition)) {
			# condition is column only
			if (is_null($parameters)) {
				return $this->addStatement('WHERE', "$condition is NULL");
			} elseif (is_array($args[1])) {
				$in = $this->quote($args[1]);
				return $this->addStatement('WHERE', "$condition IN $in");
			}
			$condition = "$condition = ?";
		}
		array_shift($args);
		return $this->addStatement('WHERE', $condition, $args);
	}

	/**
	 * @param $clause
	 * @param array $parameters - first is $statement followed by $parameters
	 * @return $this|SelectQuery
	 */
	public function __call($clause, $parameters = array()) {
		$clause = FluentUtils::toUpperWords($clause);
		if ($clause == 'GROUP') $clause = 'GROUP BY';
		if ($clause == 'ORDER') $clause = 'ORDER BY';
		if ($clause == 'FOOT NOTE') $clause = "\n--";
		$statement = array_shift($parameters);
		if (strpos($clause, 'JOIN') !== FALSE) {
			return $this->addJoinStatements($clause, $statement, $parameters);
		}
		return $this->addStatement($clause, $statement, $parameters);
	}

	protected function getClauseJoin() {
		return implode(' ', $this->statements['JOIN']);
	}

	/**
	 * Statement can contain more tables (e.g. "table1.table2:table3:")
	 * @param $clause
	 * @param $statement
	 * @param array $parameters
	 * @return $this|SelectQuery
	 */
	private function addJoinStatements($clause, $statement, $parameters = array()) {
		if ($statement === null) {
			$this->joins = array();
			return $this->resetClause('JOIN');
		}
		if (array_search(substr($statement, 0, -1), $this->joins) !== FALSE) {
			return $this;
		}

		# match "tables AS alias"
		preg_match('~`?([a-z_][a-z0-9_\.:]*)`?(\s+AS)?(\s+`?([a-z_][a-z0-9_]*)`?)?~i', $statement, $matches);
		$joinAlias = '';
		$joinTable = '';
		if ($matches) {
			$joinTable = $matches[1];
			if (isset($matches[4]) && !in_array(strtoupper($matches[4]), array('ON', 'USING'))) {
				$joinAlias = $matches[4];
			}
		}

		if (strpos(strtoupper($statement), ' ON ') || strpos(strtoupper($statement), ' USING')) {
			if (!$joinAlias) $joinAlias = $joinTable;
			if (in_array($joinAlias, $this->joins)) {
				return $this;
			} else {
				$this->joins[] = $joinAlias;
				$statement = " $clause $statement";
				return $this->addStatement('JOIN', $statement, $parameters);
			}
		}

		# $joinTable is list of tables for join e.g.: table1.table2:table3....
		if (!in_array(substr($joinTable, -1), array('.', ':'))) {
			$joinTable .= '.';
		}

		preg_match_all('~([a-z_][a-z0-9_]*[\.:]?)~i', $joinTable, $matches);
		if (isset($this->statements['FROM'])) {
			$mainTable = $this->statements['FROM'];
		} elseif (isset($this->statements['UPDATE'])) {
			$mainTable = $this->statements['UPDATE'];
		}
		$lastItem = array_pop($matches[1]);
		array_push($matches[1], $lastItem);
		foreach ($matches[1] as $joinItem) {
			if ($mainTable == substr($joinItem, 0, -1)) continue;

			# use $joinAlias only for $lastItem
			$alias = '';
			if ($joinItem == $lastItem) $alias = $joinAlias;

			$newJoin = $this->createJoinStatement($clause, $mainTable, $joinItem, $alias);
			if ($newJoin) $this->addStatement('JOIN', $newJoin, $parameters);
			$mainTable = $joinItem;
		}
		return $this;
	}

	/**
	 * Create join string
	 * @param $clause
	 * @param $mainTable
	 * @param $joinTable
	 * @param string $joinAlias
	 * @return string
	 */
	private function createJoinStatement($clause, $mainTable, $joinTable, $joinAlias = '') {
		if (in_array(substr($mainTable, -1), array(':', '.'))) {
			$mainTable = substr($mainTable, 0, -1);
		}
		$referenceDirection = substr($joinTable, -1);
		$joinTable = substr($joinTable, 0, -1);
		$asJoinAlias = '';
		if ($joinAlias) {
			$asJoinAlias = " AS $joinAlias";
		} else {
			$joinAlias = $joinTable;
		}
		if (in_array($joinAlias, $this->joins)) {
			# if join exists don't create same again
			return '';
		} else {
			$this->joins[] = $joinAlias;
		}
		if ($referenceDirection == ':') {
			# back reference
			$primaryKey = $this->getStructure()->getPrimaryKey($mainTable);
			$foreignKey = $this->getStructure()->getForeignKey($mainTable);
			return " $clause $joinTable$asJoinAlias ON $joinAlias.$foreignKey = $mainTable.$primaryKey";
		} else {
			$primaryKey = $this->getStructure()->getPrimaryKey($joinTable);
			$foreignKey = $this->getStructure()->getForeignKey($joinTable);
			return " $clause $joinTable$asJoinAlias ON $joinAlias.$primaryKey = $mainTable.$foreignKey";
		}
	}

	/**
	 * @return string
	 */
	protected function buildQuery() {
		# first create extra join from statements with columns with referenced tables
		$statementsWithReferences = array('WHERE', 'SELECT', 'GROUP BY', 'ORDER BY');
		foreach ($statementsWithReferences as $clause) {
			if (array_key_exists($clause, $this->statements)) {
				$this->statements[$clause] = array_map(array($this, 'createUndefinedJoins'), $this->statements[$clause]);
			}
		}

		return parent::buildQuery();
	}

	/** Create undefined joins from statement with column with referenced tables
	 * @param string $statement
	 * @return string  rewrited $statement (e.g. tab1.tab2:col => tab2.col)
	 */
	private function createUndefinedJoins($statement) {
		if (!$this->isSmartJoinEnabled) {
			return $statement;
		}

		preg_match_all('~\\b([a-z_][a-z0-9_.:]*[.:])[a-z_]*~i', $statement, $matches);
		foreach ($matches[1] as $join) {
			if (!in_array(substr($join, 0, -1), $this->joins)) {
				$this->addJoinStatements('LEFT JOIN', $join);
			}
		}

		# don't rewrite table from other databases
		foreach ($this->joins as $join) {
			if (strpos($join, '.') !== FALSE && strpos($statement, $join) === 0) {
				return $statement;
			}
		}

		# remove extra referenced tables (rewrite tab1.tab2:col => tab2.col)
		$statement = preg_replace('~(?:\\b[a-z_][a-z0-9_.:]*[.:])?([a-z_][a-z0-9_]*)[.:]([a-z_*])~i', '\\1.\\2', $statement);
		return $statement;
	}
}

