--TEST--
add FROM after DELETE if doesn't set
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

$query = $fpdo->delete('user', 1)->from('user');
echo $query->getQuery() . "\n";
print_r($query->getParameters()) . "\n";

?>
--EXPECTF--
DELETE user
FROM user
WHERE id = ?
Array
(
    [0] => 1
)
