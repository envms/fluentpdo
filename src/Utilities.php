<?php

namespace Envms\FluentPDO;

/**
 * Class Utilities
 */
class Utilities
{

    /**
     * Convert "camelCaseWord" to "CAMEL CASE WORD"
     *
     * @param string $string
     *
     * @return string
     */
    public static function toUpperWords($string)
    {
        $regex = new Regex();
        return trim(strtoupper($regex->camelCaseSpaced($string)));
    }

    /**
     * @param string $query
     *
     * @return string
     */
    public static function formatQuery($query)
    {
        $regex = new Regex();

        $query = $regex->splitClauses($query);
        $query = $regex->splitSubClauses($query);
        $query = $regex->removeLineEndWhitespace($query); // remove trailing spaces

        return $query;
    }

    /**
     * Converts columns from strings to types according to
     * PDOStatement::columnMeta
     * http://stackoverflow.com/a/9952703/3006989
     *
     * @param \PDOStatement      $statement
     * @param array|\Traversable $rows - provided by PDOStatement::fetch with PDO::FETCH_ASSOC
     *
     * @return array|\Traversable - copy of $assoc with matching type fields
     */
    public static function convertToNativeTypes(\PDOStatement $statement, $rows)
    {
        for ($i = 0; ($columnMeta = $statement->getColumnMeta($i)) !== false; $i++) {
            $type = $columnMeta['native_type'];

            switch ($type) {
                case 'DECIMAL':
                case 'NEWDECIMAL':
                case 'FLOAT':
                case 'DOUBLE':
                case 'TINY':
                case 'SHORT':
                case 'LONG':
                case 'LONGLONG':
                case 'INT24':
                    if (isset($rows[$columnMeta['name']])) {
                        $rows[$columnMeta['name']] = $rows[$columnMeta['name']] + 0;
                    } else {
                        if (is_array($rows) || $rows instanceof \Traversable) {
                            foreach ($rows as &$row) {
                                if (isset($row[$columnMeta['name']])) {
                                    $row[$columnMeta['name']] = $row[$columnMeta['name']] + 0;
                                }
                            }
                        }
                    }
                    break;
                case 'DATETIME':
                case 'DATE':
                case 'TIMESTAMP':
                    // convert to date type?
                    break;
                // default: keep as string
            }
        }

        return $rows;
    }

    /**
     * @param $subject
     *
     * @return bool
     */
    public static function isCountable($subject)
    {
        return (is_array($subject) || ($subject instanceof \Countable));
    }

}
