<?php

namespace Envms\FluentPDO\Queries;

use Envms\FluentPDO\{Query, Literal, Structure, Utilities};

/**
 * Base query builder
 */
abstract class Base implements \IteratorAggregate
{

    /** @var Query */
    private $fluent;

    /** @var \PDOStatement */
    private $result;

    /** @var float */
    private $time;

    /** @var bool */
    private $object = false;

    /** @var array - definition clauses */
    protected $clauses = [];
    /** @var array */
    protected $statements = [];
    /** @var array */
    protected $parameters = [];

    /**
     * BaseQuery constructor.
     *
     * @param Query $fluent
     * @param       $clauses
     */
    protected function __construct(Query $fluent, $clauses)
    {
        $this->fluent = $fluent;
        $this->clauses = $clauses;
        $this->initClauses();
    }

    /**
     * Return formatted query when request class representation
     * ie: echo $query
     *
     * @return string - formatted query
     *
     * @throws \Exception
     */
    public function __toString()
    {
        return $this->getQuery();
    }

    /**
     * Initialize statement and parameter clauses.
     */
    private function initClauses()
    {
        foreach ($this->clauses as $clause => $value) {
            if ($value) {
                $this->statements[$clause] = [];
                $this->parameters[$clause] = [];
            } else {
                $this->statements[$clause] = null;
                $this->parameters[$clause] = null;
            }
        }
    }

    /**
     * Add statement for all clauses except WHERE
     *
     * @param       $clause
     * @param       $statement
     * @param array $parameters
     *
     * @return $this
     */
    protected function addStatement($clause, $statement, $parameters = [])
    {
        if ($statement === null) {
            return $this->resetClause($clause);
        }

        if ($this->clauses[$clause]) {
            if (is_array($statement)) {
                $this->statements[$clause] = array_merge($this->statements[$clause], $statement);
            } else {
                $this->statements[$clause][] = $statement;
            }

            $this->parameters[$clause] = array_merge($this->parameters[$clause], $parameters);
        } else {
            $this->statements[$clause] = $statement;
            $this->parameters[$clause] = $parameters;
        }

        return $this;
    }

    /**
     * Add statement for all kind of clauses
     *
     * @param        $statement
     * @param string $separator - should be AND or OR
     * @param array  $parameters
     *
     * @return $this
     */
    protected function addWhereStatement($statement, string $separator = 'AND', $parameters = [])
    {
        if ($statement === null) {
            return $this->resetClause('WHERE');
        }

        if (is_array($statement)) {
            foreach ($statement as $s) {
                $this->statements['WHERE'][] = [$separator, $s];
            }
        } else {
            $this->statements['WHERE'][] = [$separator, $statement];
        }

        $this->parameters['WHERE'] = array_merge($this->parameters['WHERE'], $parameters);

        return $this;
    }

    /**
     * Remove all prev defined statements
     *
     * @param $clause
     *
     * @return $this
     */
    protected function resetClause($clause)
    {
        $this->statements[$clause] = null;
        $this->parameters[$clause] = [];
        if (isset($this->clauses[$clause]) && $this->clauses[$clause]) {
            $this->statements[$clause] = [];
        }

        return $this;
    }

    /**
     * Implements method from IteratorAggregate
     *
     * @return \PDOStatement
     *
     * @throws \Exception
     */
    public function getIterator()
    {
        return $this->execute();
    }

    /**
     * Execute query with earlier added parameters
     *
     * @return \PDOStatement
     *
     * @throws \Exception
     */
    public function execute()
    {
        $query = $this->buildQuery();
        $parameters = $this->buildParameters();

        $result = $this->fluent->getPdo()->prepare($query);

        // At this point, $result is a PDOStatement instance, or false.
        // PDO::prepare() does not reliably return errors. Some database drivers
        // do not support prepared statements, and PHP emulates them. Postgresql
        // does support prepared statements, but PHP does not call Postgresql's
        // prepare function until we call PDOStatement::execute() below.
        // If PDO::prepare() worked properly, this is where we would check
        // for prepare errors, such as invalid SQL.

        if ($this->object !== false) {
            if (class_exists($this->object)) {
                $result->setFetchMode(\PDO::FETCH_CLASS, $this->object);
            } else {
                $result->setFetchMode(\PDO::FETCH_OBJ);
            }
        } elseif ($this->fluent->getPdo()->getAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE) == \PDO::FETCH_BOTH) {
            $result->setFetchMode(\PDO::FETCH_ASSOC);
        }

        $time = microtime(true);
        if ($result && $result->execute($parameters)) {
            $this->time = microtime(true) - $time;
        } else {
            $result = false;
        }

        $this->result = $result;
        $this->debugger();

        return $result;
    }

    /**
     * Echo/pass a debug string
     *
     * @throws \Exception
     */
    private function debugger()
    {
        if ($this->fluent->debug) {
            if (!is_callable($this->fluent->debug)) {
                $backtrace = '';
                $query = $this->getQuery();
                $parameters = $this->getParameters();
                $debug = '';
                if ($parameters) {
                    $debug = '# parameters: ' . implode(', ', array_map([$this, 'quote'], $parameters)) . "\n";
                }
                $debug .= $query;
                $pattern = '(^' . preg_quote(__DIR__) . '(\\.php$|[/\\\\]))'; // can be static
                foreach (debug_backtrace() as $backtrace) {
                    if (isset($backtrace['file']) && !preg_match($pattern, $backtrace['file'])) {
                        // stop on first file outside Query source codes
                        break;
                    }
                }
                $time = sprintf('%0.3f', $this->time * 1000) . ' ms';
                $rows = ($this->result) ? $this->result->rowCount() : 0;
                $finalString = "# $backtrace[file]:$backtrace[line] ($time; rows = $rows)\n$debug\n\n";
                if (defined('STDERR')) { // if STDERR is set, send there, otherwise just output the string
                    if (is_resource(STDERR)) {
                        fwrite(STDERR, $finalString);
                    } else {
                        echo $finalString;
                    }
                } else {
                    echo $finalString;
                }
            } else {
                call_user_func($this->fluent->debug, $this);
            }
        }
    }

    /**
     * @return \PDO
     */
    protected function getPDO()
    {
        return $this->fluent->getPdo();
    }

    /**
     * @return Structure
     */
    protected function getStructure()
    {
        return $this->fluent->getStructure();
    }

    /**
     * Get PDOStatement result
     *
     * @return \PDOStatement
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Get time of execution
     *
     * @return float
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Get query parameters
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->buildParameters();
    }

    /**
     * Get query string
     *
     * @param bool $formatted - Return formatted query
     *
     * @throws \Exception
     *
     * @return string
     */
    public function getQuery($formatted = true)
    {
        $query = $this->buildQuery();

        if ($formatted) {
            $query = Utilities::formatQuery($query);
        }

        return $query;
    }

    /**
     * Generate query
     *
     * @throws \Exception
     *
     * @return string
     */
    protected function buildQuery()
    {
        $query = '';

        foreach ($this->clauses as $clause => $separator) {
            if ($this->clauseNotEmpty($clause)) {
                if ($clause === 'WHERE') {
                    $firstStatement = array_shift($this->statements[$clause]);
                    $query .= " {$clause} {$firstStatement[1]}"; // append first statement to WHERE without condition

                    if (!empty($this->statements[$clause])) {
                        foreach ($this->statements[$clause] as $statement) {
                            $query .= " {$statement[0]} {$statement[1]}";
                        }
                    }

                    // put the first statement back onto the beginning of the array in case we want to run this again
                    array_unshift($this->statements[$clause], $firstStatement);
                }
                else {
                    if (is_string($separator)) {
                        $query .= " {$clause} " . implode($separator, $this->statements[$clause]);
                    } elseif ($separator === null) {
                        $query .= " {$clause} {$this->statements[$clause]}";
                    } elseif (is_callable($separator)) {
                        $query .= call_user_func($separator);
                    } else {
                        throw new \Exception("Clause '$clause' is incorrectly set to '$separator'.");
                    }
                }
            }
        }

        return trim($query);
    }

    /**
     * @param $clause
     *
     * @return bool
     */
    private function clauseNotEmpty($clause)
    {
        if ((Utilities::isCountable($this->statements[$clause])) && $this->clauses[$clause]) {
            return (boolean)count($this->statements[$clause]);
        } else {
            return (boolean)$this->statements[$clause];
        }
    }

    /**
     * @return array
     */
    protected function buildParameters()
    {
        $parameters = [];
        foreach ($this->parameters as $clauses) {
            if (is_array($clauses)) {
                foreach ($clauses as $value) {
                    if (is_array($value) && is_string(key($value)) && substr(key($value), 0, 1) == ':') {
                        // this is named params e.g. (':name' => 'Mark')
                        $parameters = array_merge($parameters, $value);
                    } else {
                        $parameters[] = $value;
                    }
                }
            } else {
                if ($clauses) {
                    $parameters[] = $clauses;
                }
            }
        }

        return $parameters;
    }

    /**
     * @param $value
     *
     * @return string
     */
    protected function quote($value)
    {
        if (!isset($value)) {
            return "NULL";
        }

        if (is_array($value)) { // (a, b) IN ((1, 2), (3, 4))
            return "(" . implode(", ", array_map([$this, 'quote'], $value)) . ")";
        }

        $value = $this->formatValue($value);
        if (is_float($value)) {
            return sprintf("%F", $value); // otherwise depends on setlocale()
        }

        if ($value === false) {
            return "0";
        }

        if (is_int($value) || $value instanceof Literal) { // number or SQL code - for example "NOW()"
            return (string)$value;
        }

        return $this->fluent->getPdo()->quote($value);
    }

    /**
     * @param \DateTime $val
     *
     * @return string
     */
    private function formatValue($val)
    {
        if ($val instanceof \DateTime) {
            return $val->format("Y-m-d H:i:s"); // may be driver specific
        }

        return $val;
    }

    /**
     * Select an item as object
     *
     * @param  \object|boolean $object If set to true, items are returned as stdClass, otherwise a class
     *                                 name can be passed and a new instance of this class is returned.
     *                                 Can be set to false to return items as an associative array.
     *
     * @return $this
     */
    public function asObject($object = true)
    {
        $this->object = $object;

        return $this;
    }

}
