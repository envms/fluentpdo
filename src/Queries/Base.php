<?php

namespace Envms\FluentPDO\Queries;

use Envms\FluentPDO\{Exception, Literal, Query, Regex, Structure, Utilities};

/**
 * Base query builder
 */
abstract class Base implements \IteratorAggregate
{

    /** @var float */
    private $totalTime;

    /** @var float */
    private $executionTime;

    /** @var bool */
    private $object = false;

    /** @var Query */
    protected $fluent;

    /** @var \PDOStatement */
    protected $result;

    /** @var array - definition clauses */
    protected $clauses = [];
    /** @var array */
    protected $statements = [];
    /** @var array */
    protected $parameters = [];

    /** @var Regex */
    protected $regex;

    /** @var string */
    protected $message = '';

    /** @var @var int */
    protected $currentFetchMode;

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
        $this->result = null;

        $this->initClauses();

        $this->regex = new Regex();
    }

    /**
     * Return formatted query when request class representation
     * ie: echo $query
     *
     * @return string - formatted query
     *
     * @throws Exception
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
     * @throws Exception
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
     * @throws Exception
     */
    public function execute()
    {
        $startTime = microtime(true);

        $query = $this->buildQuery();
        $parameters = $this->buildParameters();

        $this->prepareQuery($query);

        if ($this->result !== false) {
            $this->setObjectFetchMode($this->result);

            $execTime = microtime(true);

            $this->executeQuery($parameters, $startTime, $execTime);
            $this->debugger();
        }

        return $this->result;
    }

    /**
     * Echo/pass a debug string
     *
     * @throws Exception
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

                foreach (debug_backtrace() as $backtrace) {
                    if (isset($backtrace['file']) && !$this->regex->compareLocation($backtrace['file'])) {
                        // stop at the first file outside the FluentPDO source
                        break;
                    }
                }

                $time = sprintf('%0.3f', $this->totalTime * 1000) . 'ms';
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
     * Get query parameters
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->buildParameters();
    }

    /**
     * @return array
     */
    public function getRawClauses()
    {
        return $this->clauses;
    }

    /**
     * @return array
     */
    public function getRawStatements()
    {
        return $this->statements;
    }

    /**
     * @return array
     */
    public function getRawParameters()
    {
        return $this->parameters;
    }

    /**
     * Gets the total time of query building, preparation and execution
     *
     * @return float
     */
    public function getTotalTime(): float
    {
        return $this->totalTime;
    }

    /**
     * Gets the query execution time
     *
     * @return float
     */
    public function getExecutionTime(): float
    {
        return $this->executionTime;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Get query string
     *
     * @param bool $formatted - Return formatted query
     *
     * @throws Exception
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
     * Converts php null values to Literal instances to be inserted into a database
     */
    protected function convertNullValues()
    {
        $filterList = ['VALUES', 'ON DUPLICATE KEY UPDATE', 'SET'];

        foreach ($this->statements as $clause => $statement) {
            if (in_array($clause, $filterList)) {
                if (isset($statement[0])) {
                    foreach ($statement[0] as $key => $value) {
                        $this->statements[$clause][0][$key] = Utilities::nullToLiteral($value);
                    }
                } else {
                    foreach ($statement as $key => $value) {
                        $this->statements[$clause][$key] = Utilities::nullToLiteral($value);
                    }
                }
            }
        }
    }

    /**
     * Generate query
     *
     * @throws Exception
     *
     * @return string
     */
    protected function buildQuery()
    {
        if ($this->fluent->convertWrite === true) {
            $this->convertNullValues();
        }

        $query = '';

        foreach ($this->clauses as $clause => $separator) {
            if ($this->clauseNotEmpty($clause)) {
                if (is_string($separator)) {
                    $query .= " {$clause} " . implode($separator, $this->statements[$clause]);
                } elseif ($separator === null) {
                    $query .= " {$clause} {$this->statements[$clause]}";
                } elseif (is_callable($separator)) {
                    $query .= call_user_func($separator);
                } else {
                    throw new Exception("Clause '$clause' is incorrectly set to '$separator'.");
                }
            }
        }

        return trim(str_replace(['\.', '\:'], ['.', ':'], $query));
    }

    /**
     * @param $clause
     *
     * @return bool
     */
    private function clauseNotEmpty($clause)
    {
        if ((Utilities::isCountable($this->statements[$clause])) && $this->clauses[$clause]) {
            return (bool)count($this->statements[$clause]);
        } else {
            return (bool)$this->statements[$clause];
        }
    }

    /**
     * @return array
     */
    protected function buildParameters(): array
    {
        $parameters = [];
        foreach ($this->parameters as $clauses) {
            if ($this->fluent->convertWrite === true) {
                $clauses = Utilities::convertSqlWriteValues($clauses);
            }

            if (is_array($clauses)) {
                foreach ($clauses as $key => $value) {
                    if (strpos($key, ':') === 0) { // these are named params e.g. (':name' => 'Mark')
                        $parameters = array_merge($parameters, [$key => $value]);
                    } else {
                        if ($value !== null) {
                            $parameters[] = $value;
                        }
                    }
                }
            } else {
                if ($clauses !== false && $clauses !== null) {
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

        if ($value === true) {
            return 1;
        }

        if ($value === false) {
            return 0;
        }

        if (is_int($value) || $value instanceof Literal) { // number or SQL code - for example "NOW()"
            return (string)$value;
        }

        return $this->fluent->getPdo()->quote($value);
    }

    /**
     * @param \DateTime $val
     *
     * @return mixed
     */
    private function formatValue($val)
    {
        if ($val instanceof \DateTime) {
            return $val->format("Y-m-d H:i:s"); // may be driver specific
        }

        return $val;
    }

    /**
     * @param string $query
     *
     * @throws Exception
     */
    private function prepareQuery($query): void
    {
        $this->result = $this->fluent->getPdo()->prepare($query);

        // At this point, $result is a PDOStatement instance, or false.
        // PDO::prepare() does not reliably return errors. Some database drivers
        // do not support prepared statements, and PHP emulates them. Postgresql
        // does support prepared statements, but PHP does not call Postgresql's
        // prepare function until we call PDOStatement::execute() below.
        // If PDO::prepare() worked properly, this is where we would check
        // for prepare errors, such as invalid SQL.

        if ($this->result === false) {
            $error = $this->fluent->getPdo()->errorInfo();
            $message = "SQLSTATE: {$error[0]} - Driver Code: {$error[1]} - Message: {$error[2]}";

            if ($this->fluent->exceptionOnError === true) {
                throw new Exception($message);
            } else {
                $this->message = $message;
            }
        }
    }

    /**
     * @param array $parameters
     * @param int   $startTime
     * @param int   $execTime
     *
     * @throws Exception
     */
    private function executeQuery($parameters, $startTime, $execTime): void
    {
        if ($this->result->execute($parameters) === true) {
            $this->executionTime = microtime(true) - $execTime;
            $this->totalTime = microtime(true) - $startTime;
        } else {
            $error = $this->result->errorInfo();
            $message = "SQLSTATE: {$error[0]} - Driver Code: {$error[1]} - Message: {$error[2]}";

            if ($this->fluent->exceptionOnError === true) {
                throw new Exception($message);
            } else {
                $this->message = $message;
            }

            $this->result = false;
        }
    }

    /**
     * @param \PDOStatement $result
     */
    private function setObjectFetchMode(\PDOStatement &$result): void
    {
        if ($this->object !== false) {
            if (class_exists($this->object)) {
                $this->currentFetchMode = \PDO::FETCH_CLASS;
                $result->setFetchMode($this->currentFetchMode, $this->object);
            } else {
                $this->currentFetchMode = \PDO::FETCH_OBJ;
                $result->setFetchMode($this->currentFetchMode);
            }
        } elseif ($this->fluent->getPdo()->getAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE) == \PDO::FETCH_BOTH) {
            $this->currentFetchMode = \PDO::FETCH_ASSOC;
            $result->setFetchMode($this->currentFetchMode);
        }
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
