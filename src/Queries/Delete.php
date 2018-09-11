<?php
namespace Envms\FluentPDO\Queries;

use Envms\FluentPDO\Query;

/**
 * DELETE query builder
 *
 * @method Delete  leftJoin(string $statement) add LEFT JOIN to query
 *                        ($statement can be 'table' name only or 'table:' means back reference)
 * @method Delete  innerJoin(string $statement) add INNER JOIN to query
 *                        ($statement can be 'table' name only or 'table:' means back reference)
 * @method Delete  from(string $table) add LIMIT to query
 * @method Delete  orderBy(string $column) add ORDER BY to query
 * @method Delete  limit(int $limit) add LIMIT to query
 */
class Delete extends Common
{

    private $ignore = false;

    /**
     * Delete constructor
     *
     * @param Query  $fluent
     */
    public function __construct(Query $fluent) {
        $clauses = array(
            'DELETE FROM' => array($this, 'getClauseDeleteFrom'),
            'DELETE'      => array($this, 'getClauseDelete'),
            'FROM'        => null,
            'JOIN'        => array($this, 'getClauseJoin'),
            'WHERE'       => ' AND ',
            'ORDER BY'    => ', ',
            'LIMIT'       => null,
        );

        parent::__construct($fluent, $clauses);

        $this->statements['DELETE FROM'] = $fluent->getTableName();
        $this->statements['DELETE']      = $fluent->getTableName();
    }

    /**
     * Forces delete operation to fail silently
     *
     * @return Delete
     */
    public function ignore() {
        $this->ignore = true;

        return $this;
    }

    /**
     * @return string
     */
    protected function buildQuery() {
        if ($this->statements['FROM']) {
            unset($this->clauses['DELETE FROM']);
        } else {
            unset($this->clauses['DELETE']);
        }

        return parent::buildQuery();
    }

    /**
     * Execute DELETE query
     *
     * @return bool
     */
    public function execute() {
        $result = parent::execute();
        if ($result) {
            return $result->rowCount();
        }

        return false;
    }

    /**
     * @return string
     */
    protected function getClauseDelete() {
        return 'DELETE' . ($this->ignore ? " IGNORE" : '') . ' ' . $this->statements['DELETE'];
    }

    /**
     * @return string
     */
    protected function getClauseDeleteFrom() {
        return 'DELETE' . ($this->ignore ? " IGNORE" : '') . ' FROM ' . $this->statements['DELETE FROM'];
    }
    
}
