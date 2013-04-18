--TEST--
Expanded delete
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

$query = $fpdo->delete('t1, t2')
	->from('t1')
	->innerJoin('t2 ON t1.id = t2.id')
	->innerJoin('t3 ON t2.id = t3.id')
	->where('t1.id', 1);

echo $query->getQuery() . "\n";
print_r($query->getParameters()) . "\n";
?>
--EXPECTF--
DELETE t1, t2
FROM t1
    INNER JOIN t2 ON t1.id = t2.id
    INNER JOIN t3 ON t2.id = t3.id
WHERE t1.id = ?
Array
(
    [0] => 1
)
