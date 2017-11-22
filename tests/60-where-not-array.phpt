--TEST--
where('NOT col', array)
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var Envms\FluentPDO\Query */

$query = $fluent->from('article')->where('NOT id', array(1,2));

echo $query->getQuery() . "\n";
?>
--EXPECTF--
SELECT article.*
FROM article
WHERE NOT id IN (1, 2)
