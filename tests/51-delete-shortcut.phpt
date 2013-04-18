--TEST--
Shortcuts for delete
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

$query = $fpdo->deleteFrom('user', 'id = 1');
echo "v1: without params\n" . $query->getQuery() . "\n";
print_r($query->getParameters()) . "\n";

$query = $fpdo->deleteFrom('user', 'id', 1);
echo "v2: with one param\n" . $query->getQuery() . "\n";
print_r($query->getParameters()) . "\n";

$query = $fpdo->deleteFrom('user', 'type = ? AND country_id = ?', 'author', 1);
echo "v3: with two params\n" . $query->getQuery() . "\n";
print_r($query->getParameters()) . "\n";


?>
--EXPECTF--
v1: without params
DELETE
FROM user
WHERE id = 1
Array
(
)
v2: with one param
DELETE
FROM user
WHERE id = ?
Array
(
    [0] => 1
)
v3: with two params
DELETE
FROM user
WHERE type = ?
    AND country_id = ?
Array
(
    [0] => author
    [1] => 1
)
