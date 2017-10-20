--TEST--
where('column', null)
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var Envms\FluentPDO\Query */

$query = $fluent->from('user')->where('type', null);

echo $query->getQuery() . "\n";
?>
--EXPECTF--
SELECT user.*
FROM user
WHERE type is NULL
