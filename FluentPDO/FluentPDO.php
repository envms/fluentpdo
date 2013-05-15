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
include_once 'FluentLiteral.php';
include_once 'BaseQuery.php';
include_once 'CommonQuery.php';
include_once 'SelectQuery.php';
include_once 'InsertQuery.php';
include_once 'UpdateQuery.php';
include_once 'DeleteQuery.php';

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
	 * @return \SelectQuery
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

	/** Create INSERT INTO query
	 *
	 * @param string $table
	 * @param array $values  you can add one or multi rows array @see docs
	 * @return \InsertQuery
	 */
	public function insertInto($table, $values = array()) {
		$query = new InsertQuery($this, $table, $values);
		return $query;
	}

	/** Create UPDATE query
	 *
	 * @param string $table
	 * @param array|string $set
	 * @param string $where
	 * @param string $whereParams one or more params for where
	 *
	 * @return \UpdateQuery
	 */
	public function update($table, $set = array(), $where = '', $whereParams = '') {
		$query = new UpdateQuery($this, $table, $set, $where);
		$query->set($set);
		$args = func_get_args();
		if (count($args) > 2) {
			array_shift($args);
			array_shift($args);
			if (is_null($args)) {
				$args = array();
			}
			$query = call_user_func_array(array($query, 'where'), $args);
		}
		return $query;
	}

	/** Create DELETE query
	 *
	 * @param string $tables
	 * @param string $where
	 * @param string $whereParams one or more params for where
	 * @return \DeleteQuery
	 */
	public function delete($tables, $where = '', $whereParams = '') {
		$query = new DeleteQuery($this, $tables);
		$args = func_get_args();
		if (count($args) > 1) {
			array_shift($args);
			if (is_null($args)) {
				$args = array();
			}
			$query = call_user_func_array(array($query, 'where'), $args);
		}
		return $query;
	}

	/** Create DELETE FROM query
	 *
	 * @param string $table
	 * @param string $where
	 * @param string $whereParams one or more params for where
	 * @return \DeleteQuery
	 */
	public function deleteFrom($table, $where = '', $whereParams = '') {
		$args = func_get_args();
		return call_user_func_array(array($this, 'delete'), $args);
	}

	/** @return \PDO
	 */
	public function getPdo() {
		return $this->pdo;
	}

	/** @return \FluentStructure
	 */
	public function getStructure() {
		return $this->structure;
	}
}
