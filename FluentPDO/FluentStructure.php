<?php

class FluentStructure {
	private $primaryKey, $foreignKey;
	
	function __construct($primaryKey = 'id', $foreignKey = '%s_id') {
		$this->primaryKey = $primaryKey;
		$this->foreignKey = $foreignKey;
	}
	
	public function getPrimaryKey($table) {
		return sprintf($this->primaryKey, $table);
	}

	public function getForeignKey($table) {
		return sprintf($this->foreignKey, $table);
	}
}
