<?php
/** Trait with same functions for UPDATE and REPLACE queries
 */

trait UpdateReplaceTrait {

    /**
     * @param string|array $fieldOrArray
     * @param string|boolean $value
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
}