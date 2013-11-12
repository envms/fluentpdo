--TEST--
Query with select, group, having, order
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

$query = $fpdo
	->from('user')
	->select(null)
	->select('count(*) AS total_count')
	->groupBy(array('id', 'name'));

echo $query->getQuery() . "\n";
print_r($query->fetch());
?>
--EXPECTF--
SELECT count(*) AS total_count
FROM user
GROUP BY id,name
Array
(
    [total_count] => 1
)
