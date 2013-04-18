--TEST--
Basic delete
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

$query = $fpdo->deleteFrom('user')
	->ignore()
	->where('id', 1);

echo $query->getQuery() . "\n";
print_r($query->getParameters()) . "\n";
?>
--EXPECTF--
DELETE IGNORE
FROM user
WHERE id = ?
Array
(
    [0] => 1
)
