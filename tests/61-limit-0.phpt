--TEST--
Check that LIMIT(0) works like in SQL
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

$query = $fpdo->from('user')->limit(0);
echo $query->getQuery() . "\n";
print_r($query->fetchAll());
?>
--EXPECTF--
SELECT user.*
FROM user
LIMIT 0
Array
(
)
