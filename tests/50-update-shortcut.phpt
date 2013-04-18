--TEST--
Basic update
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */


$query = $fpdo->update('user', array('type' => 'admin'), 'country_id = 1');
echo $query->getQuery() . "\n";
print_r($query->getParameters()) . "\n";

?>
--EXPECTF--
UPDATE user SET type = ?
WHERE country_id = 1
Array
(
    [0] => admin
)
