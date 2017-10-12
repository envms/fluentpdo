<?php

/**
 * CommonQuery add JOIN and WHERE clauses for (SELECT, UPDATE, DELETE)
 */
abstract class CommonQuery extends BaseQuery
{
    /** @var array - methods which are allowed to be call by the magic method __call() */
    private $validMethods = ['from', 'fullJoin', 'group', 'groupBy', 'having', 'innerJoin', 'join', 'leftJoin',
        'limit', 'offset', 'order', 'orderBy', 'outerJoin', 'rightJoin', 'select'];

    /** @var array - Query tables (also include table from clause FROM) */
    protected $joins = array();

    /** @var bool - Disable adding undefined joins to query? */
    protected $isSmartJoinEnabled = true;

    /**
     * @return $this
     */
    public function enableSmartJoin() {
        $this->isSmartJoinEnabled = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function disableSmartJoin() {
        $this->isSmartJoinEnabled = false;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSmartJoinEnabled() {
        return $this->isSmartJoinEnabled;
    }

    /**
     * Add where condition, more calls appends with AND
     *
     * @param string $condition  possibly containing ? or :name (PDO syntax)
     * @param mixed  $parameters array or a scalar value
     *
     * @return CommonQuery
     */
    public function where($condition, $parameters = array()) {
        if ($condition === null) {
            return $this->resetClause('WHERE');
        }

        if (!$condition) {
            return $this;
        }

        if (is_array($condition)) { // where(array("column1" => 1, "column2 > ?" => 2))
            foreach ($condition as $key => $val) {
                $this->where($key, $val);
            }

            return $this;
        }

        $args = func_get_args();

        if (count($args) == 1) {
            return $this->addStatement('WHERE', $condition);
        }

        /*
        Check that there are 2 arguments, a condition and a parameter value. If the condition contains
        a parameter, add them; it's up to the dev to be valid sql. Otherwise it's probably
        just an identifier, so construct a new condition based on the passed parameter value.
        */
        if (count($args) == 2 && !preg_match('/(\?|:\w+)/i', $condition)) {
            // condition is column only
            if (is_null($parameters)) {
                return $this->addStatement('WHERE', "$condition is NULL");
            } elseif ($args[1] === array()) {
                return $this->addStatement('WHERE', 'FALSE');
            } elseif (is_array($args[1])) {
                $in = $this->quote($args[1]);

                return $this->addStatement('WHERE', "$condition IN $in");
            }

            // don't parameterize the value if it's an instance of FluentLiteral
            if ($parameters instanceof FluentLiteral) {
                $condition = "{$condition} = {$parameters}";

                return $this->addStatement('WHERE', $condition);
            }
            else {
                $condition = "$condition = ?";
            }
        }

        array_shift($args);

        return $this->addStatement('WHERE', $condition, $args);
    }

    /**
     * @param string $name
     * @param array  $parameters - first is $statement followed by $parameters
     *
     * @return $this|SelectQuery
     */
    public function __call($name, $parameters = array()) {
        if (!in_array($name, $this->validMethods)) {
            trigger_error("Call to invalid method " . get_class($this) . "::{$name}()", E_USER_ERROR);
        }

        $clause = FluentUtils::toUpperWords($name);

        if ($clause == 'GROUP') {
            $clause = 'GROUP BY';
        }
        if ($clause == 'ORDER') {
            $clause = 'ORDER BY';
        }
        if ($clause == 'FOOT NOTE') {
            $clause = "\n--";
        }

        $statement = array_shift($parameters);

        if (strpos($clause, 'JOIN') !== false) {
            return $this->addJoinStatements($clause, $statement, $parameters);
        }

        return $this->addStatement($clause, $statement, $parameters);
    }

    /**
     * @return string
     */
    protected function getClauseJoin() {
        return implode(' ', $this->statements['JOIN']);
    }

    /**
     * Statement can contain more tables (e.g. "table1.table2:table3:")
     *
     * @param       $clause
     * @param       $statement
     * @param array $parameters
     *
     * @return $this|\SelectQuery
     */
    private function addJoinStatements($clause, $statement, $parameters = array()) {
        if ($statement === null) {
            $this->joins = array();

            return $this->resetClause('JOIN');
        }

        if (array_search(substr($statement, 0, -1), $this->joins) !== false) {
            return $this;
        }

        // match "table AS alias"
        preg_match('/`?([a-z_][a-z0-9_\.:]*)`?(\s+AS)?(\s+`?([a-z_][a-z0-9_]*)`?)?/i', $statement, $matches);
        $joinAlias = '';
        $joinTable = '';

        if ($matches) {
            $joinTable = $matches[1];
            if (isset($matches[4]) && !in_array(strtoupper($matches[4]), array('ON', 'USING'))) {
                $joinAlias = $matches[4];
            }
        }

        if (strpos(strtoupper($statement), ' ON ') || strpos(strtoupper($statement), ' USING')) {
            if (!$joinAlias) {
                $joinAlias = $joinTable;
            }
            if (in_array($joinAlias, $this->joins)) {
                return $this;
            } else {
                $this->joins[] = $joinAlias;
                $statement     = " $clause $statement";

                return $this->addStatement('JOIN', $statement, $parameters);
            }
        }

        // $joinTable is list of tables for join e.g.: table1.table2:table3....
        if (!in_array(substr($joinTable, -1), array('.', ':'))) {
            $joinTable .= '.';
        }

        preg_match_all('/([a-z_][a-z0-9_]*[\.:]?)/i', $joinTable, $matches);
        $mainTable = '';

        if (isset($this->statements['FROM'])) {
            $mainTable = $this->statements['FROM'];
        } elseif (isset($this->statements['UPDATE'])) {
            $mainTable = $this->statements['UPDATE'];
        }

        $lastItem = array_pop($matches[1]);
        array_push($matches[1], $lastItem);

        foreach ($matches[1] as $joinItem) {
            if ($mainTable == substr($joinItem, 0, -1)) {
                continue;
            }

            $alias = '';

            if ($joinItem == $lastItem) {
                $alias = $joinAlias; // use $joinAlias only for $lastItem
            }

            $newJoin = $this->createJoinStatement($clause, $mainTable, $joinItem, $alias);
            if ($newJoin) {
                $this->addStatement('JOIN', $newJoin, $parameters);
            }
            $mainTable = $joinItem;
        }

        return $this;
    }

    /**
     * Create join string
     *
     * @param        $clause
     * @param        $mainTable
     * @param        $joinTable
     * @param string $joinAlias
     *
     * @return string
     */
    private function createJoinStatement($clause, $mainTable, $joinTable, $joinAlias = '') {
        if (in_array(substr($mainTable, -1), array(':', '.'))) {
            $mainTable = substr($mainTable, 0, -1);
        }

        $referenceDirection = substr($joinTable, -1);
        $joinTable          = substr($joinTable, 0, -1);
        $asJoinAlias        = '';

        if ($joinAlias) {
            $asJoinAlias = " AS $joinAlias";
        } else {
            $joinAlias = $joinTable;
        }

        if (in_array($joinAlias, $this->joins)) { // if the join exists don't create it again
            return '';
        } else {
            $this->joins[] = $joinAlias;
        }

        if ($referenceDirection == ':') { // back reference
            $primaryKey = $this->getStructure()->getPrimaryKey($mainTable);
            $foreignKey = $this->getStructure()->getForeignKey($mainTable);

            return " $clause $joinTable$asJoinAlias ON $joinAlias.$foreignKey = $mainTable.$primaryKey";
        } else {
            $primaryKey = $this->getStructure()->getPrimaryKey($joinTable);
            $foreignKey = $this->getStructure()->getForeignKey($joinTable);

            return " $clause $joinTable$asJoinAlias ON $joinAlias.$primaryKey = $mainTable.$foreignKey";
        }
    }

    /**
     * @return string
     */
    protected function buildQuery() {
        // first create extra join from statements with columns with referenced tables
        $statementsWithReferences = array('WHERE', 'SELECT', 'GROUP BY', 'ORDER BY');

        foreach ($statementsWithReferences as $clause) {
            if (array_key_exists($clause, $this->statements)) {
                $this->statements[$clause] = array_map(array($this, 'createUndefinedJoins'), $this->statements[$clause]);
            }
        }

        return parent::buildQuery();
    }

    /**
     * Create undefined joins from statement with column with referenced tables
     *
     * @param string $statement
     *
     * @return string - the rewritten $statement (e.g. tab1.tab2:col => tab2.col)
     */
    private function createUndefinedJoins($statement) {
        if (!$this->isSmartJoinEnabled) {
            return $statement;
        }

        // matches a table name made of any printable characters followed by a dot/colon,
        // followed by any letters, numbers and most punctuation (to exclude '*')
        preg_match_all('/([^[:space:]\(\)]+[.:])[\p{L}\p{N}\p{Pd}\p{Pi}\p{Pf}\p{Pc}]*/u', $statement, $matches);

        foreach ($matches[1] as $join) {
            if (!in_array(substr($join, 0, -1), $this->joins)) {
                $this->addJoinStatements('LEFT JOIN', $join);
            }
        }

        // don't rewrite table from other databases
        foreach ($this->joins as $join) {
            if (strpos($join, '.') !== false && strpos($statement, $join) === 0) {
                return $statement;
            }
        }

        // remove extra referenced tables (rewrite tab1.tab2:col => tab2.col)
        $statement = preg_replace('/(?:[^\s]*[.:])?([^\s]+)[.:]([^\s]*)/u', '$1.$2', $statement);

        return $statement;
    }
    
}
