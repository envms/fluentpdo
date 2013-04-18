--TEST--
Basic update
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

$query = $fpdo->update('user')->set(array('name' => 'keraM', '`type`' => 'author'))->where('id', 1);
$query->execute();
echo $query->getQuery() . "\n";
print_r($query->getParameters()) . "\n";

$query = $fpdo->update('user')->set(array('name' => 'Marek', '`type`' => 'admin'))->where('id', 1);
$query->execute();
?>
--EXPECTF--
UPDATE user SET name = ?, `type` = ?
WHERE id = ?
Array
(
    [0] => keraM
    [1] => author
    [2] => 1
)
