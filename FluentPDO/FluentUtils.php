<?php

/**
 * Class FluentUtils
 */
class FluentUtils
{

    /**
     * Convert "camelCaseWord" to "CAMEL CASE WORD"
     *
     * @param string $string
     *
     * @return string
     */
    public static function toUpperWords($string) {
        return trim(strtoupper(preg_replace('/(.)([A-Z]+)/', '$1 $2', $string)));
    }

    /**
     * @param string $query
     *
     * @return string
     */
    public static function formatQuery($query) {
        $query = preg_replace(
            '/\b(WHERE|FROM|GROUP BY|HAVING|ORDER BY|LIMIT|OFFSET|UNION|ON DUPLICATE KEY UPDATE|VALUES|SET)\b/',
            "\n$0", $query
        );

        $query = preg_replace(
            '/\b(INNER|OUTER|LEFT|RIGHT|FULL|CASE|WHEN|END|ELSE|AND)\b/',
            "\n    $0", $query
        );

        $query = preg_replace("/\s+\n/", "\n", $query); // remove trailing spaces

        return $query;
    }

    /**
     * Converts columns from strings to types according to 
     * PDOStatement::columnMeta
     * http://stackoverflow.com/a/9952703/3006989
     * 
     * @param PDOStatement $statement
     * @param array|Traversable $assoc - provided by PDOStatement::fetch with PDO::FETCH_ASSOC
     * @return array|Traversable - copy of $assoc with matching type fields
     */
    public static function convertToNativeTypes(PDOStatement $statement, $rows)
    {
        for ($i = 0; $columnMeta = $statement->getColumnMeta($i); $i++)
        {
            $type = $columnMeta['native_type'];
    
            switch($type)
            {
                case 'DECIMAL':
                case 'NEWDECIMAL':
                case 'FLOAT':
                case 'DOUBLE':
                case 'TINY':
                case 'SHORT':
                case 'LONG':
                case 'LONGLONG':
                case 'INT24':
                        if(isset($rows[$columnMeta['name']])){
                            $rows[$columnMeta['name']] = $rows[$columnMeta['name']] + 0;
                        }else{
                            if(is_array($rows) || $rows instanceof Traversable){
                                foreach($rows as &$row){
                                    if(isset($row[$columnMeta['name']])){
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

}
