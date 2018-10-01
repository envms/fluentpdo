<?php
namespace Envms\FluentPDO\Queries;

use Envms\FluentPDO\{Query, Utilities};

/**
 * Class Json
 *
 * @package Envms\FluentPDO\Queries
 */
class Json extends Common
{

    /** @var mixed */
    protected $fromTable;
    /** @var mixed */
    protected $fromAlias;
    /** @var boolean */
    protected $convertTypes = false;

    /**
     * Json constructor
     *
     * @param Query  $fluent
     * @param string $table
     */
    public function __construct(Query $fluent, string $table)
    {
        $clauses = [
            'SELECT'   => ', ',
            'JOIN'     => [$this, 'getClauseJoin'],
            'WHERE'    => [$this, 'getClauseWhere'],
            'GROUP BY' => ',',
            'HAVING'   => ' AND ',
            'ORDER BY' => ', ',
            'LIMIT'    => null,
            'OFFSET'   => null,
            "\n--"     => "\n--",
        ];

        parent::__construct($fluent, $clauses);

        // initialize statements
        $tableParts = explode(' ', $table);
        $this->fromTable = reset($tableParts);
        $this->fromAlias = end($tableParts);

        $this->statements['SELECT'][] = '';
        $this->joins[] = $this->fromAlias;

        if (isset($fluent->convertTypes) && $fluent->convertTypes) {
            $this->convertTypes = true;
        }
    }

}