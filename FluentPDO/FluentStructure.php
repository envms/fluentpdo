<?php

class FluentStructure {

	private $primaryKey, $foreignKey;

	function __construct($primaryKey = 'id', $foreignKey = '%s_id') {
		if ($foreignKey === null) {
			$foreignKey = $primaryKey;
		}
		$this->primaryKey = $primaryKey;
		$this->foreignKey = $foreignKey;
	}

	public function getPrimaryKey($table) {
		return $this->key($this->primaryKey, $table);
	}

	public function getForeignKey($table) {
		return $this->key($this->foreignKey, $table);
	}

	private function key($key, $table) {
		if (is_callable($key)) {
			return $key($table);
		}
		return sprintf($key, $table);
	}
}
