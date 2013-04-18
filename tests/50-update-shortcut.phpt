--TEST--
Shortcuts for update
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

$query = $fpdo->update('user', array('type' => 'admin'), 'country_id = 1');
echo "v1: without params\n" . $query->getQuery() . "\n";
print_r($query->getParameters()) . "\n";

$query = $fpdo->update('user', array('type' => 'admin'), 'country_id', 1);
echo "v2: with one param\n" . $query->getQuery() . "\n";
print_r($query->getParameters()) . "\n";

$query = $fpdo->update('user', array('type' => 'admin'), 'country_id = ? AND id = ?', 1, 2);
echo "v3: with two params\n" . $query->getQuery() . "\n";
print_r($query->getParameters()) . "\n";

?>
--EXPECTF--
v1: without params
UPDATE user SET type = ?
WHERE country_id = 1
Array
(
    [0] => admin
)
v2: with one param
UPDATE user SET type = ?
WHERE country_id = ?
Array
(
    [0] => admin
    [1] => 1
)
v3: with two params
UPDATE user SET type = ?
WHERE country_id = ?
    AND id = ?
Array
(
    [0] => admin
    [1] => 1
    [2] => 2
)
