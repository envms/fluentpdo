<?php

/**
 * SELECT query builder
 *
 * @method SelectQuery  select(string $column) add one or more columns in SELECT to query
 * @method SelectQuery  leftJoin(string $statement) add LEFT JOIN to query
 *                        ($statement can be 'table' name only or 'table:' means back reference)
 * @method SelectQuery  innerJoin(string $statement) add INNER JOIN to query
 *                        ($statement can be 'table' name only or 'table:' means back reference)
 * @method SelectQuery  groupBy(string $column) add GROUP BY to query
 * @method SelectQuery  having(string $column) add HAVING query
 * @method SelectQuery  orderBy(string $column) add ORDER BY to query
 * @method SelectQuery  limit(int $limit) add LIMIT to query
 * @method SelectQuery  offset(int $offset) add OFFSET to query
 */
class SelectQuery extends CommonQuery implements Countable
{

    /** @var mixed */
    private $fromTable;
    /** @var mixed */
    private $fromAlias;

    /** @var boolean */
    private $convertTypes = false;

    /**
     * SelectQuery constructor.
     *
     * @param FluentPDO $fpdo
     * @param           $from
     */
    function __construct(FluentPDO $fpdo, $from) {
        $clauses = array(
            'SELECT'   => ', ',
            'FROM'     => null,
            'JOIN'     => array($this, 'getClauseJoin'),
            'WHERE'    => ' AND ',
            'GROUP BY' => ',',
            'HAVING'   => ' AND ',
            'ORDER BY' => ', ',
            'LIMIT'    => null,
            'OFFSET'   => null,
            "\n--"     => "\n--",
        );
        parent::__construct($fpdo, $clauses);

        // initialize statements
        $fromParts       = explode(' ', $from);
        $this->fromTable = reset($fromParts);
        $this->fromAlias = end($fromParts);

        $this->statements['FROM']     = $from;
        $this->statements['SELECT'][] = $this->fromAlias . '.*';
        $this->joins[]                = $this->fromAlias;

        if(isset($fpdo->convertTypes) && $fpdo->convertTypes){
            $this->convertTypes = true;
        }
    }

    /** Return table name from FROM clause
     *
     * @internal
     */
    public function getFromTable() {
        return $this->fromTable;
    }

    /** Return table alias from FROM clause
     *
     * @internal
     */
    public function getFromAlias() {
        return $this->fromAlias;
    }

    /**
     * Returns a single column
     *
     * @param int $columnNumber
     *
     * @return string
     */
    public function fetchColumn($columnNumber = 0) {
        if (($s = $this->execute()) !== false) {
            return $s->fetchColumn($columnNumber);
        }

        return $s;
    }

    /**
     * Fetch first row or column
     *
     * @param string $column column name or empty string for the whole row
     *
     * @return mixed string, array or false if there is no row
     */
    public function fetch($column = '') {
        $s = $this->execute();
        if ($s === false) {
            return false;
        }
        $row = $s->fetch();

        if($this->convertTypes){
            $row = FluentUtils::convertToNativeTypes($s,$row);
        }

        if ($row && $column != '') {
            if (is_object($row)) {
                return $row->{$column};
            } else {
                return $row[$column];
            }
        }

        return $row;
    }

    /**
     * Fetch pairs
     *
     * @param $key
     * @param $value
     * @param $object
     *
     * @return array of fetched rows as pairs
     */
    public function fetchPairs($key, $value, $object = false) {
        if (($s = $this->select(null)->select("$key, $value")->asObject($object)->execute()) !== false) {
            return $s->fetchAll(PDO::FETCH_KEY_PAIR);
        }

        return $s;
    }

    /** Fetch all row
     *
     * @param string $index      specify index column
     * @param string $selectOnly select columns which could be fetched
     *
     * @return array of fetched rows
     */
    public function fetchAll($index = '', $selectOnly = '') {
        if ($selectOnly) {
            $this->select(null)->select($index . ', ' . $selectOnly);
        }
        if ($index) {
            $data = array();
            foreach ($this as $row) {
                if (is_object($row)) {
                    $data[$row->{$index}] = $row;
                } else {
                    $data[$row[$index]] = $row;
                }
            }

            return $data;
        } else {
            if (($s = $this->execute()) !== false) {
                if($this->convertTypes){
                    return FluentUtils::convertToNativeTypes($s,$s->fetchAll());
                }else{
                    return $s->fetchAll();
                }
            }

            return $s;
        }
    }

    /**
     * Countable interface doesn't break current \FluentPDO select query
     *
     * @return int
     */
    public function count() {
        $fpdo = clone $this;

        return (int)$fpdo->select(null)->select('COUNT(*)')->fetchColumn();
    }

    public function getIterator() {
        if ($this->convertTypes) {
            return new ArrayIterator($this->fetchAll());
        } else {
            return $this->execute();
        }
    }
    
}
