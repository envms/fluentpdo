<?php
/** 
 * FluentPDO is simple and smart SQL query builder for PDO
 * 
 * FluentPDO was inspired by very good NotORM library. I use it often but
 * sometimes I need to build a large query *"in classical way"* with many joins 
 * and clauses and with full control over generated query string. 
 * For this reason I created FluentPDO.
 * 
 * For more information @see readme.md
 * 
 * @link http://github.com/lichtner/fluentpdo
 * @author Marek Lichtner, marek@licht.sk
 * @copyright 2012 Marek Lichtner
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
 */
class FluentPDO {
	
	private $pdo, $structure;
	
	function __construct($pdo, $structure = null) {
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
		$query = new FluentQuery($this->pdo, $this->structure, $table);
		if ($id) {
			$primary = $this->structure->getPrimaryKey($table);
			$query = $query->where("$table.$primary = ?", $id);
		}
		return $query;
    }
}

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

/** 
 * SQL Fluent query builder
 * 
 * @method FluentQuery  select(string $column) add one or more columns in SELECT to query
 * @method FluentQuery  leftJoin(string $statement) add LEFT JOIN to query 
 *						($statement can be 'table' name only or 'table:' means back reference)
 * @method FluentQuery  innerJoin(string $statement) add INNER JOIN to query 
 *						($statement can be 'table' name only or 'table:' means back reference)
 * @method FluentQuery  groupBy(string $column) add GROUP BY to query
 * @method FluentQuery  having(string $column) add HAVING query
 * @method FluentQuery  orderBy(string $column) add ORDER BY to query
 * @method FluentQuery  limit(int $limit) add LIMIT to query
 * @method FluentQuery  offset(int $offset) add OFFSET to query
 */
class FluentQuery {
	
	/** @var PDO */
	private $pdo;
	/** @var FluentStructure */
	private $structure;
	private $clauses = array(), $statements = array(), $parameters = array();
	private $joins = array();

	function __construct($pdo, $structure, $from) {
		$this->pdo = $pdo;
		$this->structure = $structure;
		$this->createSelectClauses();
		$this->statements['FROM'] = $from;
		$this->statements['SELECT'][] = "$from.*";
	}

	private function createSelectClauses() {
		$this->clauses = array(
			'SELECT' => ', ',
			'FROM' => null,
			'JOIN' => ' ',
			'WHERE' => ' AND ',
			'GROUP BY' => ',',
			'HAVING' => ' AND ',
			'ORDER BY' => ', ',
			'LIMIT' => null,
			'OFFSET' => null,
		);
		$this->initClauses();
	}
	
	private function initClauses() {
		foreach ($this->clauses as $clause => $value) {
			if ($value) {
				$this->statements[$clause] = array();
				$this->parameters[$clause] = array();
			} else {
				$this->statements[$clause] = null;
				$this->parameters[$clause] = null;
			}
		}
	}
	
	/** Add SQL clause with parameters
	 * @param type $clause
	 * @param type $parameters  first is $statement followed by $parameters
	 * @return FluentQuery
	 */
	function __call($clause, $parameters = array()) {
		$clause = FluentUtils::toUpperWords($clause);
		$statement = array_shift($parameters);
		if (strpos($clause, 'JOIN')) {
			return $this->addJoinStatements($clause, $statement, $parameters);
		}
		return $this->addStatement($clause, $statement, $parameters);
	}
	
	/** add statement for all kind of clauses
	 * @return FluentQuery 
	 */
	private function addStatement($clause, $statement, $parameters = array()) {
		if ($statement === null) {
			return $this->resetClause($clause);
		}
		if (in_array($clause, array('WHERE', 'SELECT', 'ORDER BY', 'GROUP BY'))) {
			$statement = $this->createUndefinedJoins($statement);
		}
		# $statement !== null 
		if ($this->clauses[$clause]) {
			$this->statements[$clause][] = $statement;
			$this->parameters[$clause] = array_merge($this->parameters[$clause], $parameters);
		} else {
			$this->statements[$clause] = $statement;
			$this->parameters[$clause] = $parameters;
		}
		return $this;
	}
	
	/** Remove all prev defined statements 
	 * @return FluentQuery 
	 */
	private function resetClause($clause) {
		$this->statements[$clause] = null;
		if ($this->clauses[$clause]) {
			$this->statements[$clause] = array();
		}
		return $this;
	}
	
	/** Create undefined joins from statement and return rewrited statement
	 * @param string $statement
	 * @return string  rewrited $statement
	 */
	private function createUndefinedJoins($statement) {
		preg_match_all('~\\b([a-z_][a-z0-9_.:]*[.:])[a-z_]*~i', $statement, $matches);
		foreach ($matches[1] as $join) {
			if (!in_array(substr($join, 0, -1), $this->joins)) {
				$this->addJoinStatements('LEFT JOIN', $join);
			}
		}
		#remove extra dots => rewrite tab1.tab2.col
		$statement = preg_replace('~(?:\\b[a-z_][a-z0-9_.:]*[.:])?([a-z_][a-z0-9_]*)[.:]([a-z_*])~i', '\\1.\\2', $statement);
		return $statement;
	}

	/** Statement can contain more tables (e.g. "table1.table2:table3:")
	 * @return FluentQuery 
	 */
	private function addJoinStatements($clause, $statement, $parameters = array()) {
		if ($statement === null) {
			$this->joins = array();
			return $this->resetClause('JOIN');
		}
		
		# match "tables AS alias"
		preg_match('~([a-z_][a-z0-9_\.:]*)(\s+AS)?(\s+([a-z_][a-z0-9_]*))?~i', $statement, $matches);
		$joinAlias = '';
		if ($matches) {
			$joinTable = $matches[1];
			if (isset($matches[4]) && strtoupper($matches[4]) != 'ON') {
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
		$mainTable = $this->statements['FROM'];
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

	/** Create join string
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
			$primaryKey = $this->structure->getPrimaryKey($mainTable);
			$foreignKey = $this->structure->getForeignKey($mainTable);
			return " $clause $joinTable$asJoinAlias ON $joinAlias.$foreignKey = $mainTable.$primaryKey";
		} else {
			$primaryKey = $this->structure->getPrimaryKey($joinTable);
			$foreignKey = $this->structure->getForeignKey($joinTable);
			return " $clause $joinTable$asJoinAlias ON $joinAlias.$primaryKey = $mainTable.$foreignKey";
		}
	}
	
	/** Add where condition, more calls appends with AND
	* @param string $condition  possibly containing ? or :name (PDO syntax)
	* @param mixed $parameters  array or a scalar value
	* @return FluentQuery 
	*/
	function where($condition, $parameters = array()) {
		if (is_array($condition)) { // where(array("column1" => 1, "column2 > ?" => 2))
			foreach ($condition as $key => $val) {
				$this->where($key, $val);
			}
			return $this;
		}
		if ($condition === null) {
			return $this->resetClause('WHERE');
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
	
	/** Execute query with earlier added parameters
	 * @return PDOStatement
	 */
	function execute() {
		$query = $this->buildQuery();
		$parameters = $this->buildParameters();
        $result = $this->pdo->prepare($query);
        $result->setFetchMode(PDO::FETCH_ASSOC);
        return ($result && $result->execute($parameters) ? $result : false);
	}
	
	/** Get added parameters (for debug purpose)
	 * @return array
	 */
	function getParameters() {
		return $this->buildParameters();
	}
	
	/** Get built query (for debug purpose)
	 * @param boolean $formated  return formated query
	 * @return string
	 */
	function getQuery($formated = true) {
		$query = $this->buildQuery();
		if ($formated) $query = FluentUtils::formatQuery($query);
		return $query;
	}
	
	/**
	 * @return string
	 */
	private function buildQuery() {
		$query = '';
		foreach ($this->clauses as $clause => $separator) {
			if ($this->clauseNotEmpty($clause)) {
				if ($clause !== 'JOIN') $query .= " $clause ";
				if (is_array($this->statements[$clause])) {
					 $query .= implode($separator, $this->statements[$clause]);
				} else {
					$query .= $this->statements[$clause];
				}
			}
		} 
		return trim($query);
	}
	
	private function clauseNotEmpty($clause) {
		if ($this->clauses[$clause]) {
			return (boolean) count($this->statements[$clause]);
		} else {
			return (boolean) $this->statements[$clause];
		}
	}

	private function buildParameters() {
		$parameters = array();
		foreach ($this->parameters as $clauses) {
			if (is_array($clauses)) {
				foreach ($clauses as $value) {
					if (is_array($value) && is_string(key($value)) && substr(key($value),0,1) == ':') {
						// this is named params e.g. (':name' => 'Mark')
						$parameters = array_merge($parameters, $value);
					} else {
						$parameters[] = $value;
					}
				}
			} else {
				if ($clauses) $parameters[] = $clauses;
			}
		}
		return $parameters;
	}
	
	private function quote($value) {
		if (!isset($value)) {
			return "NULL";
		}
		if (is_array($value)) { // (a, b) IN ((1, 2), (3, 4))
			return "(" . implode(", ", array_map(array($this, 'quote'), $value)) . ")";
		}
		$value = $this->formatValue($value);
		if (is_float($value)) {
			return sprintf("%F", $value); // otherwise depends on setlocale()
		}
		if ($value === false) {
			return "0";
		}
		if (is_int($value)) { # @todo maybe add FluentLiteral
			return (string) $value;
		}
		return $this->pdo->quote($value);
	}
	
	private function formatValue($val) {
		if ($val instanceof DateTime) {
			return $val->format("Y-m-d H:i:s"); //! may be driver specific
		}
		return $val;
	}
	
}

class FluentUtils {
	
	/** Convert "camelCaseWord" to "CAMEL CASE WORD"
	 * @param string $string
	 * @return string 
	 */
	public static function toUpperWords($string) {
		return trim(strtoupper(preg_replace('#(.)([A-Z]+)#', '$1 $2', $string)));
	}

	public static function formatQuery($query) {
		$query = preg_replace(
			'/WHERE|FROM|GROUP BY|HAVING|ORDER BY|LIMIT|OFFSET|UNION|DUPLICATE KEY/',
			"\n$0", $query
		);
		$query = preg_replace(
			'/INNER|LEFT|RIGHT|CASE|WHEN|END|ELSE|AND/',
			"\n    $0", $query
		);
		return $query;
	}
}