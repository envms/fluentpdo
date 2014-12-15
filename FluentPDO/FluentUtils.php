<?php

class FluentUtils {

	/** Convert "camelCaseWord" to "CAMEL CASE WORD"
	 * @param string $string
	 * @return string
	 */
	public static function toUpperWords($string) {
		return trim(strtoupper(preg_replace('#(.)([A-Z]+)#', '$1 $2', $string)));
	}

	public static function formatQuery($query) {
		$query = preg_replace(
			'/WHERE|FROM|GROUP BY|HAVING|ORDER BY|LIMIT|OFFSET|UNION|ON DUPLICATE KEY UPDATE|VALUES/',
			"\n$0", $query
		);
		$query = preg_replace(
			'/INNER|LEFT|RIGHT|CASE|WHEN|END|ELSE|AND/',
			"\n    $0", $query
		);
		# remove trailing spaces
		$query = preg_replace("/\s+\n/", "\n", $query);
		return $query;
	}

    /** Fills in query placeholders with their parameter values; useful only for debugging.
     * @param string $query
     * @param array parameters
     */
    public static function populate($query, array $parameters) {
        foreach ($parameters as $parameter_value) {
            // Quoting mechanism.
            if(!in_array($parameter_value, array(
                'NULL',
                'NOW()',
                // Add more exceptions here if needed.
            ))) {
                $parameter_value = sprintf("'%s'", $parameter_value);
            }
            $query = preg_replace('%\?%', $parameter_value, $query, 1);
        }
        return $query;
    }
}
