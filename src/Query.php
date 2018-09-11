<?php
namespace Envms\FluentPDO;

use Envms\FluentPDO\Queries\{Insert,Select,Update,Delete};


/**
 * FluentPDO is a quick and light PHP library for rapid query building. It features a smart join builder, which automatically creates table joins.
 *
 * For more information see readme.md
 *
 * @link      http://github.com/envms/fluentpdo
 * @author    envms, start@env.ms
 * @copyright 2012-2017 env.ms - Chris Bornhoft, Aldo Matelli, Stefan Yohansson, Kevin Sanabria, Carol Zhang, Marek Lichtner
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
 */

/**
 * Class Query
 */
class Query
{

    /** @var \PDO */
    protected $pdo;
    /** @var Structure|null */
    protected $structure;

    /** @var bool|callback */
    public $debug;

    /** @var boolean */
    public $convertTypes = false;

    /** @var string */
    protected $table;
    /** @var string */
    protected $prefix;
    /** @var string */
    protected $separator;

    /**
     * Query constructor
     *
     * @param \PDO           $pdo
     * @param Structure|null $structure
     */
    function __construct(\PDO $pdo, Structure $structure = null) {
        $this->pdo = $pdo;
        if (!$structure) {
            $structure = new Structure();
        }
        $this->structure = $structure;
    }

    /**
     * Create SELECT query from $table
     *
     * @param string  $table      - db table name
     * @param integer $primaryKey - return one row by primary key
     *
     * @return Select
     */
    public function from($table, $primaryKey = null) {
        $query = new Select($this, $table);
        if ($primaryKey !== null) {
            $tableTable     = $query->getFromTable();
            $tableAlias     = $query->getFromAlias();
            $primaryKeyName = $this->structure->getPrimaryKey($tableTable);
            $query          = $query->where("$tableAlias.$primaryKeyName", $primaryKey);
        }

        return $query;
    }

    /**
     * Create INSERT INTO query
     *
     * @param string $table
     * @param array  $values - accepts one or multiple rows, @see docs
     *
     * @return Insert
     */
    public function insertInto($table, $values = array()) {
        $query = new Insert($this, $table, $values);

        return $query;
    }

    /**
     * Create UPDATE query
     *
     * @param array|string $set
     * @param string       $primaryKey
     *
     * @throws \Exception
     * @return Update
     */
    public function update($set = array(), $primaryKey = null) {
        if(empty($this->table)){
            throw new \Exception('Table name is not set');
        } else {
            $query = new Update($this);
        }

        $query->set($set);
        if ($primaryKey) {
            $primaryKeyName = $this->getStructure()->getPrimaryKey($this->table);
            $query          = $query->where($primaryKeyName, $primaryKey);
        }

        return $query;
    }

    /**
     * Create DELETE query
     *
     * @param string $table
     * @param string $primaryKey delete only row by primary key
     *
     * @throws \Exception
     * @return Delete
     */
    public function delete($primaryKey = null) {
        if(empty($this->table)){
            throw new \Exception('Table name is not set');
        } else {
            $query = new Delete($this);
        }

        if ($primaryKey) {
            $primaryKeyName = $this->getStructure()->getPrimaryKey($this->table);
            $query          = $query->where($primaryKeyName, $primaryKey);
        }

        return $query;
    }

    /**
     * Create DELETE FROM query
     *
     * @param string $table
     * @param string $primaryKey
     *
     * @return Delete
     */
    public function deleteFrom($table, $primaryKey = null) {
        $args = func_get_args();

        return call_user_func_array(array($this, 'delete'), $args);
    }

    /**
     * @return \PDO
     */
    public function getPdo() {
        return $this->pdo;
    }

    /**
     * @return Structure
     */
    public function getStructure() {
        return $this->structure;
    }

    /**
     * Closes the \PDO connection to the database
     *
     * @return null
     */
    public function close() {
        $this->pdo = null;
    }

    /**
     * Set table name comprised of prefix.separator.table
     *
     */
    public function setTableName($table = '', $prefix = '', $separator = ''){
        $this->prefix    = $prefix;
        $this->separator = $separator;

        $this->table     = $prefix.$separator.$table;

        return $this;
    }

    /**
     * Return table name
     *
     * @return string
     *
     */
    public function getTableName() {
        return $this->table;
    }

}
