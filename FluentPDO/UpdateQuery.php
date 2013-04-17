<?php

/** UPDATE query builder
 *
 */
class UpdateQuery extends CommonQuery
{

	public function __construct(FluentPDO $fpdo, $table)
	{
		$clauses = array(
			'UPDATE' => array($this, 'getClauseUpdate'),
			'SET' => array($this, 'getClauseSet'),
			'WHERE' => ' AND '
		);
		parent::__construct($fpdo, $clauses);

		$this->statements['UPDATE'] = $table;
	}

	public function set($field, $value = '')
	{
		$this->statements['SET'][$field] = $value;
		return $this;
	}

	/** Execute update query
	 * @return boolean
	 */
	public function execute()
	{
		$result = parent::execute();
		if($result)
		{
			return true;
		}
		return false;
	}

	protected function getClauseUpdate()
	{
		return 'UPDATE ' . $this->statements['UPDATE'];
	}

	protected function getClauseSet()
	{
		$fieldList = array();
		foreach($this->statements['SET'] as $field => $value)
		{
			array_push($fieldList, $field . ' = ?');
			$this->parameters['SET'] = $value;
		}
		return ' SET ' . implode(', ', $fieldList);
	}
}



