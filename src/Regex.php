<?php

namespace Envms\FluentPDO;

/**
 * Regex class
 */
class Regex
{
    /** @var string - All UTF-8 letter characters */
    public const ALPHA = '\p{L}';
    /** @var string - All UTF-8 letter and number characters */
    public const ALNUM = '\p{L}\p{N}';
    /** @var string - All valid SQL characters except the UTF-8 groupings with quotes and wildcards */
    public const SQLCHARS = '\p{L}\p{N}\p{Pc}\p{Pd}\p{Pf}\p{Pi}';

    /**
     * Replace "camelCaseMethod" with "camel Case Method"
     *
     * @param $subject
     *
     * @return null|string|string[]
     */
    public function camelCaseSpaced($subject)
    {
        return preg_replace('/(.)([A-Z]+)/', '$1 $2', $subject);
    }

    /**
     * Replace "SELECT * FROM table WHERE column = ?" with
     * "SELECT *
     *  FROM table
     *  WHERE column = ?"
     *
     * @param $subject
     *
     * @return null|string|string[]
     */
    public function splitClauses($subject)
    {
        return preg_replace(
            '/\b(WHERE|FROM|GROUP BY|HAVING|ORDER BY|LIMIT|OFFSET|UNION|ON DUPLICATE KEY UPDATE|VALUES|SET)\b/',
            "\n$0", $subject
        );
    }

    /**
     * Replace SELECT t2.id FROM t1 LEFT JOIN t2 ON t2.id = t1.t2_id" with
     * "SELECT t2.id FROM t1
     *      LEFT JOIN t2 ON t2.id = t1.t2_id"
     *
     * @param $subject
     *
     * @return null|string|string[]
     */
    public function splitSubClauses($subject)
    {
        return preg_replace(
            '/\b(INNER|OUTER|LEFT|RIGHT|FULL|CASE|WHEN|END|ELSE|AND|OR)\b/',
            "\n    $0", $subject
        );
    }

    /**
     * Replace "WHERE column = ?  " with "WHERE column = ?"
     *
     * @param $subject
     *
     * @return null|string|string[]
     */
    public function removeLineEndWhitespace($subject)
    {
        return preg_replace("/\s+\n/", "\n", $subject);
    }

    /**
     * Replace the string "table1.table2.column" with "table2.column"
     *
     * @param $subject
     *
     * @return null|string|string[]
     */
    public function removeAdditionalJoins($subject) {
        return preg_replace('/(?:[^\s]*[.:])?([^\s]+)[.:]([^\s]*)/u', '$1.$2', $subject);
    }

    /**
     * Match the first file outside of the Fluent source
     *
     * @param        $subject
     * @param string $extension
     *
     * @return false|int
     */
    public function localFile($subject, $extension = 'php')
    {
        return preg_match('/(^' . preg_quote(__DIR__) . '(\\.' . $extension . '$|[/\\\\]))/', $subject);
    }

    /**
     * Match the string "?" or ":param"
     *
     * @param $subject
     *
     * @return false|int
     */
    public function sqlParameter($subject)
    {
        return preg_match('/(\?|:\w+)/i', $subject);
    }

    /**
     * Match the UTF-8 string "table AS alias"
     *
     * @param      $subject
     * @param null $matches
     *
     * @return false|int
     */
    public function tableAlias($subject, &$matches = null)
    {
        return preg_match('/`?([' . self::SQLCHARS . ']+[.:]?[' . self::SQLCHARS . ']*)`?(\s+AS)?(\s+`?([' . self::SQLCHARS . ']*)`?)?/ui',
            $subject, $matches);
    }

    /**
     * Match the UTF-8 string "table" or "table."
     *
     * @param $subject
     * @param $matches
     *
     * @return false|int
     */
    public function tableJoin($subject, &$matches)
    {
        return preg_match_all('/([' . self::SQLCHARS . ']+[.:]?)/u', $subject, $matches);
    }

    /**
     * Match the UTF-8 string "table." or "table.column"
     *
     * @param $subject
     * @param $matches
     *
     * @return false|int
     */
    public function tableJoinFull($subject, &$matches)
    {
        return preg_match_all('/([^[:space:]\(\)]+[.:])[' . self::SQLCHARS . ']*/u', $subject, $matches);
    }

}
