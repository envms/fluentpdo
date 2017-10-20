--TEST--
add FROM after DELETE if doesn't set
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var Envms\FluentPDO\Query */

$query = $fluent->delete('user', 1)->from('user');
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
