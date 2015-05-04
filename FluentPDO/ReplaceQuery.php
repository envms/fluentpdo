<?php
/** REPLACE INTO query builder
 */
class ReplaceQuery extends BaseQuery {

    public function __construct(FluentPDO $fpdo, $table) {
        $clauses = array(
            'REPLACE INTO' => array($this, 'getClauseReplaceInto'),
            'SET' => array($this, 'getClauseSet'),
        );
        parent::__construct($fpdo, $clauses);

        $this->statements['REPLACE INTO'] = $table;
    }

    /**
     * @param string|array $fieldOrArray
     * @param null $value
     * @return $this
     * @throws Exception
     */
    public function set($fieldOrArray, $value = false) {
        if (!$fieldOrArray) {
            return $this;
        }
        if (is_string($fieldOrArray) && $value !== false) {
            $this->statements['SET'][$fieldOrArray] = $value;
        } else {
            if (!is_array($fieldOrArray)) {
                throw new Exception('You must pass a value, or provide the SET list as an associative array. column => value');
            } else {
                foreach ($fieldOrArray as $field => $value) {
                    $this->statements['SET'][$field] = $value;
                }
            }
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