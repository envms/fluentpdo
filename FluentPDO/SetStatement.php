<?php
class SetStatement {
    private $setStatement, $parameters;

    public function set($fieldOrArray, $value = false) {
        if (!$fieldOrArray) {
            return [];
        }

        if (is_string($fieldOrArray) && $value !== false) {
            $this->setStatement[$fieldOrArray] = $value;
        } else {
            if (!is_array($fieldOrArray)) {
                throw new Exception('You must pass a value, or provide the SET list as an associative array. column => value');
            } else {
                foreach ($fieldOrArray as $field => $value) {
                    $this->setStatement[$field] = $value;
                }
            }
        }
        return $this->setStatement;
    }


    public function getClauseSet() {
        $setArray = array();
        foreach ($this->setStatement as $field => $value) {
            if ($value instanceof FluentLiteral) {
                $setArray[] = $field . ' = ' . $value;
            } else {
                $setArray[] = $field . ' = ?';
                $this->parameters[$field] = $value;
            }
        }

        return ' SET ' . implode(', ', $setArray);
    }

    public function getParameters() {
        return $this->parameters;
    }
}