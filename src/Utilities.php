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
     * @param \PDOStatement $statement
     * @param array|\Traversable $rows - provided by PDOStatement::fetch with PDO::FETCH_ASSOC
     * @return array|\Traversable - copy of $assoc with matching type fields
     */
    public static function convertToNativeTypes(\PDOStatement $statement, $rows)
    {
        for ($i = 0; ($columnMeta = $statement->getColumnMeta($i)) !== false; $i++)
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
                            if(is_array($rows) || $rows instanceof \Traversable){
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

    /**
     *
     *Converts PHP datatype to MySQL accept values
     *
     *.
     * @param $value mixed
     * @return boolean
     */
    public static function acceptedSqlTypes($value)
    {
        $count = count($value);
        for ($i = 0; $i < $count ; $i++)
        {

            $type = gettype($value);

            switch($type)
            {
                case 'boolean':
/*                    if ($value === true){
                        $value = 1;
                    } else{
                        $value = 0;
                    }
                    return $value;*/
                    return true;
                    break;
                case 'string':
                    break;
                case 'integer':
                    return true;
                case 'double':
                case 'array':
                case 'object':
                default:
                    return false;
                    break;
            }
        }

        return false;
    }

    /**
     * @param $subject
     *
     * @return bool
     */
    public static function isCountable($subject) {
        return (is_array($subject) || ($subject instanceof \Countable));
    }

}
