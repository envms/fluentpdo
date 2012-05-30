<?php
/**
 * FluentPDO is simple and smart SQL query builder for PDO
 *
 * For more information @see readme.md
 *
 * @link http://github.com/lichtner/fluentpdo
 * @author Marek Lichtner, marek@licht.sk
 * @copyright 2012 Marek Lichtner
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
 */

include_once 'FluentStructure.php';
include_once 'FluentUtils.php';
include_once 'BaseQuery.php';
include_once 'CommonQuery.php';
include_once 'SelectQuery.php';
include_once 'InsertQuery.php';

class FluentPDO {

	private $pdo, $structure;

	/** @var boolean|callback */
	public $debug;

	function __construct(PDO $pdo, FluentStructure $structure = null) {
		$this->pdo = $pdo;
		if (!$structure) {
			$structure = new FluentStructure;
		}
		$this->structure = $structure;
	}

	/** Create SELECT query from $table
	 * @param string $table  db table name
	 * @param integer $id  return one row by primary key
	 * @return FluentQuery
	 */
	public function from($table, $id = null) {
		$query = new SelectQuery($this, $table);
		if ($id) {
			$tableTable = $query->getFromTable();
			$tableAlias = $query->getFromAlias();
			$primary = $this->structure->getPrimaryKey($tableTable);
			$query = $query->where("$tableAlias.$primary = ?", $id);
		}
		return $query;
	}

	public function insertInto($table, $values = array()) {
		$query = new InsertQuery($this, $table, $values);
		return $query;
	}

	/**
	 * @return PDO
	 */
	public function getPdo() {
		return $this->pdo;
	}

	/**
	 * @return FluentStructure
	 */
	public function getStructure() {
		return $this->structure;
	}
}
