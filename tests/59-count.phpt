--TEST--
Count operations
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

$query = $fpdo->from('user')->where('id > ?', 0)->orderBy('name');
echo $query->selectCount() . "\n";
echo count($query) . "\n";
?>
--EXPECTF--
2
2
