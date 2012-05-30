<?php

/** INSERT query builder
 */
class InsertQuery extends BaseQuery {

	private $columns = array();
	private $firstValue = array();

	public function __construct(FluentPDO $fpdo, $table, $values) {
		$clauses = array(
			'INSERT INTO' => null,
			'VALUES' => array($this, 'getClauseValues'),
			'ON DUPLICATE KEY UPDATE' => array($this, 'getClauseOnDuplicateKeyUpdate'),
		);
		parent::__construct($fpdo, $clauses);

		$this->statements['INSERT INTO'] = $table;
		$this->values($values);
	}

	public function execute() {
		$result = parent::execute();
		if ($result) {
			return $this->getPDO()->lastInsertId();
		}
		return false;
	}

	protected function getClauseValues() {
		$valuesArray = array();
		foreach ($this->statements['VALUES'] as $rows) {
			$quoted = array_map(array($this, 'quote'), $rows);
			$valuesArray[] = '(' . implode(', ', $quoted) . ')';
		}
		$columns = implode(', ', $this->columns);
		$values = implode(', ', $valuesArray);
		return " ($columns) VALUES $values";
	}

	protected function getClauseOnDuplicateKeyUpdate() {
		$result = array();
		foreach ($this->statements['ON DUPLICATE KEY UPDATE'] as $key => $value) {
			$result[] = "$key = " . $this->quote($value);
		}
		return ' ON DUPLICATE KEY UPDATE ' . implode(', ', $result);
	}

	public function onDuplicateKeyUpdate($values) {
		$this->statements['ON DUPLICATE KEY UPDATE'] = array_merge(
				$this->statements['ON DUPLICATE KEY UPDATE'], $values
			);
		return $this;
	}

	public function values($values) {
		if (!is_array($values)) {
			throw new Exception('Param VALUES for INSERT query must be array');
		}
		$first = current($values);
		if (is_string(key($values))) {
			# is one row array
			$this->addOneValue($values);
		} elseif (is_array($first) && is_string(key($first))) {
			# this is multi values
			foreach ($values as $oneValue) {
				$this->addOneValue($oneValue);
			}
		}
		return $this;
	}

	private function addOneValue($oneValue) {
		# check if all $keys are strings
		foreach ($oneValue as $key => $value) {
			if (!is_string($key)) {
				throw new Exception('INSERT query: All keys of value array have to be strings.');
			}
		}
		if (!$this->firstValue) {
			$this->firstValue = $oneValue;
		}
		if (!$this->columns) {
			$this->columns = array_keys($oneValue);
		}
		if ($this->columns != array_keys($oneValue)) {
			throw new Exception('INSERT query: All VALUES have to same keys (columns).');
		}
		$this->statements['VALUES'][] = $oneValue;
	}

}



