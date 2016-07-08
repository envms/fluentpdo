<?php

/** INSERT query builder
 */
class InsertQuery extends BaseQuery
{

    /** @var array */
    private $columns = array();

    /** @var array */
    private $firstValue = array();

    /** @var bool */
    private $ignore = false;
    /** @var bool */
    private $delayed = false;

    /**
     * InsertQuery constructor.
     *
     * @param FluentPDO $fpdo
     * @param string    $table
     * @param           $values
     */
    public function __construct(FluentPDO $fpdo, $table, $values) {
        $clauses = array(
            'INSERT INTO'             => array($this, 'getClauseInsertInto'),
            'VALUES'                  => array($this, 'getClauseValues'),
            'ON DUPLICATE KEY UPDATE' => array($this, 'getClauseOnDuplicateKeyUpdate'),
        );
        parent::__construct($fpdo, $clauses);

        $this->statements['INSERT INTO'] = $table;
        $this->values($values);
    }

    /**
     * Execute insert query
     * 
     * @param mixed $sequence
     *
     * @return integer last inserted id or false
     */
    public function execute($sequence = null) {
        $result = parent::execute();
        if ($result) {
            return $this->getPDO()->lastInsertId($sequence);
        }

        return false;
    }

    /**
     * Add ON DUPLICATE KEY UPDATE
     *
     * @param array $values
     *
     * @return \InsertQuery
     */
    public function onDuplicateKeyUpdate($values) {
        $this->statements['ON DUPLICATE KEY UPDATE'] = array_merge(
            $this->statements['ON DUPLICATE KEY UPDATE'], $values
        );

        return $this;
    }

    /**
     * Add VALUES
     *
     * @param $values
     *
     * @return \InsertQuery
     * @throws Exception
     */
    public function values($values) {
        if (!is_array($values)) {
            throw new Exception('Param VALUES for INSERT query must be array');
        }
        $first = current($values);
        if (is_string(key($values))) {
            // is one row array
            $this->addOneValue($values);
        } elseif (is_array($first) && is_string(key($first))) {
            // this is multi values
            foreach ($values as $oneValue) {
                $this->addOneValue($oneValue);
            }
        }

        return $this;
    }

    /**
     * Force insert operation to fail silently
     *
     * @return \InsertQuery
     */
    public function ignore() {
        $this->ignore = true;

        return $this;
    }

    /** Force insert operation delay support
     *
     * @return \InsertQuery
     */
    public function delayed() {
        $this->delayed = true;

        return $this;
    }

    /**
     * @return string
     */
    protected function getClauseInsertInto() {
        return 'INSERT' . ($this->ignore ? " IGNORE" : '') . ($this->delayed ? " DELAYED" : '') . ' INTO ' . $this->statements['INSERT INTO'];
    }

    /**
     * @param $param
     *
     * @return string
     */
    protected function parameterGetValue($param) {
        return $param instanceof FluentLiteral ? (string)$param : '?';
    }

    /**
     * @return string
     */
    protected function getClauseValues() {
        $valuesArray = array();
        foreach ($this->statements['VALUES'] as $rows) {
            // literals should not be parametrized.
            // They are commonly used to call engine functions or literals.
            // Eg: NOW(), CURRENT_TIMESTAMP etc
            $placeholders  = array_map(array($this, 'parameterGetValue'), $rows);
            $valuesArray[] = '(' . implode(', ', $placeholders) . ')';
        }

        $columns = implode(', ', $this->columns);
        $values  = implode(', ', $valuesArray);

        return " ($columns) VALUES $values";
    }

    /**
     * Removes all FluentLiteral instances from the argument
     * since they are not to be used as PDO parameters but rather injected directly into the query
     *
     * @param $statements
     *
     * @return array
     */
    protected function filterLiterals($statements) {
        $f = function ($item) {
            return !$item instanceof FluentLiteral;
        };

        return array_map(function ($item) use ($f) {
            if (is_array($item)) {
                return array_filter($item, $f);
            }

            return $item;
        }, array_filter($statements, $f));
    }

    /**
     * @return array
     */
    protected function buildParameters() {
        $this->parameters = array_merge(
            $this->filterLiterals($this->statements['VALUES']),
            $this->filterLiterals($this->statements['ON DUPLICATE KEY UPDATE'])
        );

        return parent::buildParameters();
    }

    /**
     * @return string
     */
    protected function getClauseOnDuplicateKeyUpdate() {
        $result = array();
        foreach ($this->statements['ON DUPLICATE KEY UPDATE'] as $key => $value) {
            $result[] = "$key = " . $this->parameterGetValue($value);
        }

        return ' ON DUPLICATE KEY UPDATE ' . implode(', ', $result);
    }

    /**
     * @param array $oneValue
     *
     * @throws Exception
     */
    private function addOneValue($oneValue) {
        // check if all $keys are strings
        foreach ($oneValue as $key => $value) {
            if (!is_string($key)) {
                throw new Exception('INSERT query: All keys of value array have to be strings.');
            }
        }
        if (!$this->firstValue) {
            $this->firstValue = $oneValue;
        }
        if (!$this->columns) {
            $this->columns = array_keys($oneValue);
        }
        if ($this->columns != array_keys($oneValue)) {
            throw new Exception('INSERT query: All VALUES have to same keys (columns).');
        }
        $this->statements['VALUES'][] = $oneValue;
    }

}
