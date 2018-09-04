<?php
namespace Envms\FluentPDO\Queries;

use Envms\FluentPDO\{Query,Literal};

/**
 * UPDATE query builder
 *
 * @method Update  leftJoin(string $statement) add LEFT JOIN to query
 *                        ($statement can be 'table' name only or 'table:' means back reference)
 * @method Update  innerJoin(string $statement) add INNER JOIN to query
 *                        ($statement can be 'table' name only or 'table:' means back reference)
 * @method Update  orderBy(string $column) add ORDER BY to query
 * @method Update  limit(int $limit) add LIMIT to query
 */
class Update extends Common
{

    /**
     * UpdateQuery constructor
     *
     * @param Query     $fluent
     * @param           $table
     */
    public function __construct(Query $fluent, $table, $jsonFunction = ''){
        $clauses = array(
            'UPDATE'   => array($this, 'getClauseUpdate'),
            'JOIN'     => array($this, 'getClauseJoin'),
            'SET'      => array($this, 'getClauseSet'),
            'WHERE'    => ' AND ',
            'ORDER BY' => ', ',
            'LIMIT'    => null,
        );
        parent::__construct($fluent, $clauses);

        $this->statements['UPDATE'] = $table;

        $this->jsonFunction    = $jsonFunction;

        $tableParts    = explode(' ', $table);
        $this->joins[] = end($tableParts);
    }

    /**
     * @param string|array $fieldOrArray
     * @param bool|string  $value
     *
     * @return $this
     * @throws \Exception
     */
    public function set($fieldOrArray, $value = false, $jsonUpdateValue = '')
    {
        if (!$fieldOrArray) {
            return $this;
        }
        if (is_string($fieldOrArray) && $value !== false) {
            $this->statements['SET'][$fieldOrArray] = $value;
            if ($jsonUpdateValue != ''){
                if (is_array($jsonUpdateValue)){
                    $this->jsonPath = 'JSON_OBJECT('.$jsonUpdateValue[0].', '.$jsonUpdateValue[1].')';
                } else {
                    $this->jsonPath = $jsonUpdateValue;
                }
            }
        }
        else {
            if (!is_array($fieldOrArray)) {
                throw new \Exception('You must pass a value, or provide the SET list as an associative array. column => value');
            } else {
                foreach ($fieldOrArray as $field => $value) {
                    $this->statements['SET'][$field] = $value;
                }
            }
        }

        return $this;
    }

    /**
     * Execute update query
     *
     * @param boolean $getResultAsPdoStatement true to return the pdo statement instead of row count
     *
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

    /**
     * @return string
     */
    protected function getClauseUpdate() {
        return 'UPDATE ' . $this->statements['UPDATE'];
    }

    /**
     * @return string
     */
    protected function getClauseSet() {
        $setArray = array();
        foreach ($this->statements['SET'] as $field => $value) {
            if ($this->jsonFunction !== ''){
                if (!empty($this->jsonPath)) {
                    $setArray[] = $field . ' = ' . $this->jsonFunction . '(' . $field . ', $' . $value . ', '. $this->jsonPath .')';
                } else {
                    $setArray[] = $field . ' = ' . $this->jsonFunction . '(' . $field . ', $' . $value . ')';
                }
            }
            else if ($value instanceof Literal) {
                $setArray[] = $field . ' = ' . $value;
            } else {
                $setArray[]                      = $field . ' = ?';
                $this->parameters['SET'][$field] = $value;
            }
        }

        return ' SET ' . implode(', ', $setArray);
    }

}
