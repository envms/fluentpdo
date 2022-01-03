<?php

namespace Envms\FluentPDO\Queries;

use Envms\FluentPDO\{Exception, Literal, Utilities};

/**
 * CommonQuery add JOIN and WHERE clauses for (SELECT, UPDATE, DELETE)
 *
 * @method $this from(string $table) - add FROM to DELETE query
 * @method $this leftJoin(string $statement) - add LEFT JOIN to query
 *         $statement can be the 'table' name only or 'table:' to back reference the join
 * @method $this rightJoin(string $statement) - add RIGHT JOIN to query
 * @method $this innerJoin(string $statement) - add INNER JOIN to query
 * @method $this outerJoin(string $statement) - add OUTER JOIN to query
 * @method $this fullJoin(string $statement) - add FULL JOIN to query
 * @method $this group(string $column) - add GROUP BY to query
 * @method $this groupBy(string $column) - add GROUP BY to query
 * @method $this having(string $column) - add HAVING query
 * @method $this order(string $column) - add ORDER BY to query
 * @method $this orderBy(string $column) - add ORDER BY to query
 * @method $this limit(int $limit) - add LIMIT to query
 * @method $this offset(int $offset) - add OFFSET to query
 * @method $this comment(string $comment) - add COMMENT (--) to query
 */
abstract class Common extends Base
{

    /** @var array - methods which are allowed to be called by the magic method __call() */
    private $validMethods = [
        'comment',
        'from',
        'fullJoin',
        'group',
        'groupBy',
        'having',
        'innerJoin',
        'join',
        'leftJoin',
        'limit',
        'offset',
        'order',
        'orderBy',
        'outerJoin',
        'rightJoin'
    ];

    /** @var array - Query tables (also include table from clause FROM) */
    protected $joins = [];

    /** @var bool - Disable adding undefined joins to query? */
    protected $isSmartJoinEnabled = true;

    /**
     * @param string $name
     * @param array  $parameters - first is $statement followed by $parameters
     *
     * @return $this
     */
    public function __call($name, $parameters = [])
    {
        if (!in_array($name, $this->validMethods)) {
            trigger_error("Call to invalid method " . get_class($this) . "::{$name}()", E_USER_ERROR);
        }

        $clause = Utilities::toUpperWords($name);

        if ($clause == 'GROUP' || $clause == 'ORDER') {
            $clause = "{$clause} BY";
        }

        if ($clause == 'COMMENT') {
            $clause = "\n--";
        }

        $statement = array_shift($parameters);

        if (strpos($clause, 'JOIN') !== false) {
            return $this->addJoinStatements($clause, $statement, $parameters);
        }

        return $this->addStatement($clause, $statement, $parameters);
    }

    /**
     * @return $this
     */
    public function enableSmartJoin()
    {
        $this->isSmartJoinEnabled = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function disableSmartJoin()
    {
        $this->isSmartJoinEnabled = false;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSmartJoinEnabled()
    {
        return $this->isSmartJoinEnabled;
    }

    /**
     * Add where condition, defaults to appending with AND
     *
     * @param string|array $condition  - possibly containing ? or :name (PDO syntax)
     * @param mixed        $parameters
     * @param string       $separator - should be AND or OR
     *
     * @return $this
     */
    public function where($condition, $parameters = [], $separator = 'AND')
    {
        if ($condition === null) {
            return $this->resetClause('WHERE');
        }

        if (!$condition) {
            return $this;
        }

        if (is_array($condition)) { // where(["column1 > ?" => 1, "column2 < ?" => 2])
            foreach ($condition as $key => $val) {
                $this->where($key, $val);
            }

            return $this;
        }

        $args = func_get_args();

        if ($parameters === []) {
            return $this->addWhereStatement($condition, $separator);
        }

        /*
         * Check that there are 2 arguments, a condition and a parameter value. If the condition contains
         * a parameter (? or :name), add them; it's up to the dev to be valid sql. Otherwise it's probably
         * just an identifier, so construct a new condition based on the passed parameter value.
         */
        if (count($args) >= 2 && !$this->regex->sqlParameter($condition)) {
            // condition is column only
            if (is_null($parameters)) {
                return $this->addWhereStatement("$condition IS NULL", $separator);
            } elseif ($args[1] === []) {
                return $this->addWhereStatement('FALSE', $separator);
            } elseif (is_array($args[1])) {
                $in = $this->quote($args[1]);

                return $this->addWhereStatement("$condition IN $in", $separator);
            }

            // don't parameterize the value if it's an instance of Literal
            if ($parameters instanceof Literal) {
                $condition = "{$condition} = {$parameters}";

                return $this->addWhereStatement($condition, $separator);
            } else {
                $condition = "$condition = ?";
            }
        }

        $args = [0 => $args[1]];

        // parameters can be passed as [1, 2, 3] and it will fill a condition like: id IN (?, ?, ?)
        if (is_array($parameters) && !empty($parameters)) {
            $args = $parameters;
        }

        return $this->addWhereStatement($condition, $separator, $args);
    }

    /**
     * Add where appending with OR
     *
     * @param string $condition  - possibly containing ? or :name (PDO syntax)
     * @param mixed  $parameters
     *
     * @return $this
     */
    public function whereOr($condition, $parameters = [])
    {
        if (is_array($condition)) { // where(["column1 > ?" => 1, "column2 < ?" => 2])
            foreach ($condition as $key => $val) {
                $this->whereOr($key, $val);
            }

            return $this;
        }

        return $this->where($condition, $parameters, 'OR');
    }

    /**
     * @return string
     */
    protected function getClauseJoin()
    {
        return implode(' ', $this->statements['JOIN']);
    }

    /**
     * @return string
     */
    protected function getClauseWhere() {
        $firstStatement = array_shift($this->statements['WHERE']);
        $query = " WHERE {$firstStatement[1]}"; // append first statement to WHERE without condition

        if (!empty($this->statements['WHERE'])) {
            foreach ($this->statements['WHERE'] as $statement) {
                $query .= " {$statement[0]} {$statement[1]}"; // [0] -> AND/OR [1] -> field = ?
            }
        }

        // put the first statement back onto the beginning of the array in case we want to run this again
        array_unshift($this->statements['WHERE'], $firstStatement);

        return $query;
    }

    /**
     * Statement can contain more tables (e.g. "table1.table2:table3:")
     *
     * @param       $clause
     * @param       $statement
     * @param array $parameters
     *
     * @return $this
     */
    private function addJoinStatements($clause, $statement, $parameters = [])
    {
        if ($statement === null) {
            $this->joins = [];

            return $this->resetClause('JOIN');
        }

        if (array_search(substr($statement, 0, -1), $this->joins) !== false) {
            return $this;
        }

        list($joinAlias, $joinTable) = $this->setJoinNameAlias($statement);

        if (strpos(strtoupper($statement), ' ON ') !== false || strpos(strtoupper($statement), ' USING') !== false) {
            return $this->addRawJoins($clause, $statement, $parameters, $joinAlias, $joinTable);
        }

        $mainTable = $this->setMainTable();

        // if $joinTable does not end with a dot or colon, append one
        if (!in_array(substr($joinTable, -1), ['.', ':'])) {
            $joinTable .= '.';
        }

        $this->regex->tableJoin($joinTable, $matches);

        // used for applying the table alias
        $lastItem = array_pop($matches[1]);
        array_push($matches[1], $lastItem);

        foreach ($matches[1] as $joinItem) {
            if ($this->matchTableWithJoin($mainTable, $joinItem)) {
                // this is still the same table so we don't need to add the same join
                continue;
            }

            $mainTable = $this->applyTableJoin($clause, $parameters, $mainTable, $joinItem, $lastItem, $joinAlias);
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
    private function createJoinStatement($clause, $mainTable, $joinTable, $joinAlias = '')
    {
        if (in_array(substr($mainTable, -1), [':', '.'])) {
            $mainTable = substr($mainTable, 0, -1);
        }

        $referenceDirection = substr($joinTable, -1);
        $joinTable = substr($joinTable, 0, -1);
        $asJoinAlias = '';

        if (!empty($joinAlias)) {
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
     * Create undefined joins from statement with column with referenced tables
     *
     * @param string $statement
     *
     * @return string - the rewritten $statement (e.g. tab1.tab2:col => tab2.col)
     */
    private function createUndefinedJoins($statement)
    {
        if ($this->isEscapedJoin($statement)) {
            return $statement;
        }

        $separator = null;
        // if we're in here, this is a where clause
        if (is_array($statement)) {
            $separator = $statement[0];
            $statement = $statement[1];
        }

        // matches a table name made of any printable characters followed by a dot/colon,
        // followed by any letters, numbers and most punctuation (to exclude '*')
        $this->regex->tableJoinFull($statement, $matches);

        foreach ($matches[1] as $join) {
            // remove the trailing dot and compare with the joins we already have
            if (!in_array(substr($join, 0, -1), $this->joins)) {
                $this->addJoinStatements('LEFT JOIN', $join);
            }
        }

        // don't rewrite table from other databases
        foreach ($this->joins as $join) {
            if (strpos($join, '.') !== false && strpos($statement, $join) === 0) {
                // rebuild the where statement
                if ($separator !== null) {
                    $statement = [$separator, $statement];
                }
                
                return $statement;
            }
        }

        $statement = $this->regex->removeAdditionalJoins($statement);

        // rebuild the where statement
        if ($separator !== null) {
            $statement = [$separator, $statement];
        }

        return $statement;
    }

    /**
     * @throws Exception
     *
     * @return string
     */
    protected function buildQuery()
    {
        // first create extra join from statements with columns with referenced tables
        $statementsWithReferences = ['WHERE', 'SELECT', 'GROUP BY', 'ORDER BY'];

        foreach ($statementsWithReferences as $clause) {
            if (array_key_exists($clause, $this->statements)) {
                $this->statements[$clause] = array_map([$this, 'createUndefinedJoins'], $this->statements[$clause]);
            }
        }

        return parent::buildQuery();
    }

    /**
     * @param $statement
     *
     * @return bool
     */
    protected function isEscapedJoin($statement)
    {
        if (is_array($statement)) {
            $statement = $statement[1];
        }

        return !$this->isSmartJoinEnabled || strpos($statement, '\.') !== false || strpos($statement, '\:') !== false;
    }

    /**
     * @param $statement
     *
     * @return array
     */
    private function setJoinNameAlias($statement)
    {
        $this->regex->tableAlias($statement, $matches); // store any found alias in $matches
        $joinAlias = '';
        $joinTable = '';

        if ($matches) {
            $joinTable = $matches[1];
            if (isset($matches[4]) && !in_array(strtoupper($matches[4]), ['ON', 'USING'])) {
                $joinAlias = $matches[4];
            }
        }

        return [$joinAlias, $joinTable];
    }

    /**
     * @param $table
     * @param $joinItem
     *
     * @return bool
     */
    private function matchTableWithJoin($table, $joinItem)
    {
        return $table == substr($joinItem, 0, -1);
    }

    /**
     * @param $clause
     * @param $statement
     * @param $parameters
     * @param $joinAlias
     * @param $joinTable
     *
     * @return $this
     */
    private function addRawJoins($clause, $statement, $parameters, $joinAlias, $joinTable)
    {
        if (!$joinAlias) {
            $joinAlias = $joinTable;
        }

        if (in_array($joinAlias, $this->joins)) {
            return $this;
        } else {
            $this->joins[] = $joinAlias;
            $statement = " $clause $statement";

            return $this->addStatement('JOIN', $statement, $parameters);
        }
    }

    /**
     * @return string
     */
    private function setMainTable()
    {
        if (isset($this->statements['FROM'])) {
            return $this->statements['FROM'];
        } elseif (isset($this->statements['UPDATE'])) {
            return $this->statements['UPDATE'];
        }

        return '';
    }

    /**
     * @param $clause
     * @param $parameters
     * @param $mainTable
     * @param $joinItem
     * @param $lastItem
     * @param $joinAlias
     *
     * @return mixed
     */
    private function applyTableJoin($clause, $parameters, $mainTable, $joinItem, $lastItem, $joinAlias)
    {
        $alias = '';

        if ($joinItem == $lastItem) {
            $alias = $joinAlias; // use $joinAlias only for $lastItem
        }

        $newJoin = $this->createJoinStatement($clause, $mainTable, $joinItem, $alias);

        if ($newJoin) {
            $this->addStatement('JOIN', $newJoin, $parameters);
        }

        return $joinItem;
    }

    public function __clone()
    {
        foreach ($this->clauses as $clause => $value) {
            if (is_array($value) && $value[0] instanceof Common) {
                $this->clauses[$clause][0] = $this;
            }
        }
    }
}
