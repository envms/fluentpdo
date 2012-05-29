--TEST--
where('column', null)
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

$query = $fpdo->from('user')->where('type', null);

echo $query->getQuery() . "\n";
?>
--EXPECTF--
SELECT user.*
FROM user
WHERE type is NULL
