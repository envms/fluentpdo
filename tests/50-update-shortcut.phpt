--TEST--
Shortcuts for update
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

$query = $fpdo->update('user', array('type' => 'admin'), 1);
echo $query->getQuery() . "\n";
print_r($query->getParameters()) . "\n";

?>
--EXPECTF--
UPDATE user SET type = ?
WHERE id = ?
Array
(
    [0] => admin
    [1] => 1
)
