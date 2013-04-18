<?php

/** DELETE query builder
 *
 * @method DeleteQuery  orderBy(string $column) add ORDER BY to query
 * @method DeleteQuery  limit(int $limit) add LIMIT to query
 */
class DeleteQuery extends CommonQuery {

	private $ignore = false;

	public function __construct(FluentPDO $fpdo, $table) {
		$clauses = array(
			'DELETE FROM' => array($this, 'getClauseDelete'),
			'WHERE' => ' AND ',
			'ORDER BY' => ', ',
			'LIMIT' => null,
		);
		parent::__construct($fpdo, $clauses);

		$this->statements['DELETE FROM'] = $table;

		$tableParts = explode(' ', $table);
		$this->joins[] = end($tableParts);
	}

	/** DELETE IGNORE - Delete operation fails silently
	 * @return \DeleteQuery
	 */
	public function ignore() {
		$this->ignore = true;
		return $this;
	}

	/** Execute DELETE query
	 * @return boolean
	 */
	public function execute() {
		$result = parent::execute();
		if ($result) {
			$result->rowCount();
		}
		return false;
	}

	protected function getClauseDelete() {
		return 'DELETE' . ($this->ignore ? " IGNORE" : ''). ' FROM ' . $this->statements['DELETE FROM'];
	}
}