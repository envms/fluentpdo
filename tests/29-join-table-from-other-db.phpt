--TEST--
FROM table from other database
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

$query = $fpdo->from('user')
		->innerJoin('db2.types ON db2.types.id = user.type')
		->select('db2.types.*')
		->getQuery();
echo "$query\n";

?>
--EXPECTF--
SELECT user.*, db2.types.*
FROM user
    INNER JOIN db2.types ON db2.types.id = user.type
