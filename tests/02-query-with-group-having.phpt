--TEST--
Query with select, group, having, order
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

$query = $fpdo
	->from('user')
	->select(null)
	->select('type, count(id) AS type_count')
	->where('id > ?', 1)
	->groupBy('type')
	->having('type_count > ?', 1)
	->orderBy('name');

echo $query->getQuery() . "\n";
?>
--EXPECTF--
SELECT type, count(id) AS type_count
FROM user
WHERE id > ?
GROUP BY type
HAVING type_count > ?
ORDER BY name
