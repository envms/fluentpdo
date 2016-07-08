<?php

/**
 * DELETE query builder
 *
 * @method DeleteQuery  leftJoin(string $statement) add LEFT JOIN to query
 *                        ($statement can be 'table' name only or 'table:' means back reference)
 * @method DeleteQuery  innerJoin(string $statement) add INNER JOIN to query
 *                        ($statement can be 'table' name only or 'table:' means back reference)
 * @method DeleteQuery  from(string $table) add LIMIT to query
 * @method DeleteQuery  orderBy(string $column) add ORDER BY to query
 * @method DeleteQuery  limit(int $limit) add LIMIT to query
 */
class DeleteQuery extends CommonQuery
{

    private $ignore = false;

    /**
     * DeleteQuery constructor.
     *
     * @param FluentPDO $fpdo
     * @param string    $table
     */
    public function __construct(FluentPDO $fpdo, $table) {
        $clauses = array(
            'DELETE FROM' => array($this, 'getClauseDeleteFrom'),
            'DELETE'      => array($this, 'getClauseDelete'),
            'FROM'        => null,
            'JOIN'        => array($this, 'getClauseJoin'),
            'WHERE'       => ' AND ',
            'ORDER BY'    => ', ',
            'LIMIT'       => null,
        );

        parent::__construct($fpdo, $clauses);

        $this->statements['DELETE FROM'] = $table;
        $this->statements['DELETE']      = $table;
    }

    /**
     * Forces delete operation to fail silently
     *
     * @return \DeleteQuery
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
