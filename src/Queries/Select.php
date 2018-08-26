<?php
namespace Envms\FluentPDO\Queries;

use Envms\FluentPDO\{Query,Utilities};

/**
 * SELECT query builder
 *
 * @method Select  select(string $column) add one or more columns in SELECT to query
 * @method Select  leftJoin(string $statement) add LEFT JOIN to query
 *                        ($statement can be 'table' name only or 'table:' means back reference)
 * @method Select  innerJoin(string $statement) add INNER JOIN to query
 *                        ($statement can be 'table' name only or 'table:' means back reference)
 * @method Select  groupBy(string $column) add GROUP BY to query
 * @method Select  having(string $column) add HAVING query
 * @method Select  orderBy(string $column) add ORDER BY to query
 * @method Select  limit(int $limit) add LIMIT to query
 * @method Select  offset(int $offset) add OFFSET to query
 */
class Select extends Common implements \Countable
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
     * @param Query     $fluent
     * @param           $from
     */
    function __construct(Query $fluent, $from) {
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
        parent::__construct($fluent, $clauses);

        // initialize statements
        $fromParts       = explode(' ', $from);
        $this->fromTable = reset($fromParts);
        $this->fromAlias = end($fromParts);

        $this->statements['FROM']     = $from;
        $this->statements['SELECT'][] = $this->fromAlias . '.*';
        $this->joins[]                = $this->fromAlias;

        if(isset($fluent->convertTypes) && $fluent->convertTypes){
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
     * @todo rename either $s (which is a PDOStatement), or the Select::fetch() function
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
            $row = Utilities::convertToNativeTypes($s,$row);
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
     * @todo fix return value on query failure
     *
     * @param $key
     * @param $value
     * @param $object
     *
     * @return array of fetched rows as pairs
     */
    public function fetchPairs($key, $value, $object = false) {
        if (($s = $this->select(null)->select("$key, $value")->asObject($object)->execute()) !== false) {
            return $s->fetchAll(\PDO::FETCH_KEY_PAIR);
        }

        return $s;
    }

    /** Fetch all row
     *
     * @param string $index      specify index column
     * @param string $selectOnly select columns which could be fetched
     *
     * @return \PDOStatement|array of fetched rows
     */
    public function fetchAll($index = '', $selectOnly = '') {
        if ($selectOnly) {
            $this->select(null)->select($index . ', ' . $selectOnly);
        }
        if ($index) {
            $data = [];
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
                    return Utilities::convertToNativeTypes($s, $s->fetchAll());
                } else {
                    return $s->fetchAll();
                }
            }

            return $s;
        }
    }

    /**
     * \Countable interface doesn't break current select query
     *
     * @return int
     */
    public function count() {
        $fluent = clone $this;

        return (int)$fluent->select(null)->select('COUNT(*)')->fetchColumn();
    }

    /**
     * @return \ArrayIterator|\PDOStatement
     * @todo look into \Countable implementation
     */
    public function getIterator() {
        if ($this->convertTypes) {
            return new \ArrayIterator($this->fetchAll());
        } else {
            return $this->execute();
        }
    }
    
}
