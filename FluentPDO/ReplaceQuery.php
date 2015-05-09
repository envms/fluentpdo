<?php
/** REPLACE INTO query builder
 */
class ReplaceQuery extends BaseQuery {
    use UpdateReplaceTrait;

    public function __construct(FluentPDO $fpdo, $table) {
        $clauses = array(
            'REPLACE INTO' => array($this, 'getClauseReplaceInto'),
            'SET' => array($this, 'getClauseSet'),
        );
        parent::__construct($fpdo, $clauses);

        $this->statements['REPLACE INTO'] = $table;
    }

    /** Alias for SET statement
     * @param $values
     * @return $this
     * @throws Exception
     */
    public function values($values) {
        if (!is_array($values)) {
            throw new Exception('Param VALUES for REPLACE query must be array');
        }
        $this->set($values);

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

    protected function getClauseReplaceInto() {
        return 'REPLACE INTO ' . $this->statements['REPLACE INTO'];
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