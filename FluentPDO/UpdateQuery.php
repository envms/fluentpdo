<?php

/** UPDATE query builder
 *
 * @method UpdateQuery  leftJoin(string $statement) add LEFT JOIN to query
 *                        ($statement can be 'table' name only or 'table:' means back reference)
 * @method UpdateQuery  innerJoin(string $statement) add INNER JOIN to query
 *                        ($statement can be 'table' name only or 'table:' means back reference)
 * @method UpdateQuery  orderBy(string $column) add ORDER BY to query
 * @method UpdateQuery  limit(int $limit) add LIMIT to query
 */
class UpdateQuery extends CommonQuery {
    private $setStatement;

	public function __construct(FluentPDO $fpdo, $table) {
		$clauses = array(
			'UPDATE' => array($this, 'getClauseUpdate'),
			'JOIN' => array($this, 'getClauseJoin'),
			'SET' => array($this, 'getClauseSet'),
			'WHERE' => ' AND ',
			'ORDER BY' => ', ',
			'LIMIT' => null,
		);
		parent::__construct($fpdo, $clauses);

		$this->statements['UPDATE'] = $table;

		$tableParts = explode(' ', $table);
		$this->joins[] = end($tableParts);
        $this->setStatement = new SetStatement();
	}

	/**
	 * @param string|array $fieldOrArray
	 * @param null $value
	 * @return $this
	 * @throws Exception
	 */
    public function set($fieldOrArray, $value = false) {
        $values = $this->setStatement->set($fieldOrArray, $value);
        if (!$values) {
            return $this;
        }
        foreach ($values as $field => $value) {
            $this->statements['SET'][$field] = $value;
        }
        return $this;
    }

	/** Execute update query
	 * @param boolean $getResultAsPdoStatement true to return the pdo statement instead of row count
	 * @return int|boolean|\PDOStatement
	 */
	public function execute($getResultAsPdoStatement = false) {
		$result = parent::execute();
		if ($getResultAsPdoStatement) {
			return $result;
		}
		if ($result) {
			return $result->rowCount();
		}
		return false;
	}

	protected function getClauseUpdate() {
		return 'UPDATE ' . $this->statements['UPDATE'];
	}

	protected function getClauseSet() {
        $set = $this->setStatement->getClauseSet();
        $this->parameters['SET'] = $this->setStatement->getParameters();
		return $set;
	}
}



