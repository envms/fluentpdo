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
    use UpdateReplaceTrait;

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
	}

	protected function getClauseUpdate() {
		return 'UPDATE ' . $this->statements['UPDATE'];
	}

	protected function getClauseSet() {
		$setArray = array();
		foreach ($this->statements['SET'] as $field => $value) {
			if ($value instanceof FluentLiteral) {
				$setArray[] = $field . ' = ' . $value;
			} else {
				$setArray[] = $field . ' = ?';
				$this->parameters['SET'][$field] = $value;
			}
		}

		return ' SET ' . implode(', ', $setArray);
	}
}



