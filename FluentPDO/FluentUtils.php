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
}