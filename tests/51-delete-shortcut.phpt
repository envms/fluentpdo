--TEST--
Shortcuts for delete
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

$query = $fpdo->deleteFrom('user', 1);
echo $query->getQuery() . "\n";
print_r($query->getParameters()) . "\n";

?>
--EXPECTF--
DELETE
FROM user
WHERE id = ?
Array
(
    [0] => 1
)
