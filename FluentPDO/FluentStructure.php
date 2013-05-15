<?php

class FluentStructure {
	private $primaryKey, $foreignKey;
	
	function __construct($primaryKey = 'id', $foreignKey = '%s_id') {
		$this->primaryKey = $primaryKey;
		$this->foreignKey = $foreignKey;
	}
	
	public function getPrimaryKey($table) {
		if(is_callable($this->primaryKey)) {
			$method = $this->primaryKey;
			return $method($table);
		}
		return sprintf($this->primaryKey, $table);
	}

	public function getForeignKey($table) {
		if(is_callable($this->foreignKey)) {
			$method = $this->foreignKey;
			return $method($table);
		}
		return sprintf($this->foreignKey, $table);
	}
}
