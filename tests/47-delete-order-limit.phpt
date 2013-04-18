--TEST--
Delete with ORDER BY and LIMIT
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

$query = $fpdo->deleteFrom('user')
	->where('id', 2)
	->orderBy('name')
	->limit(1);

echo $query->getQuery() . "\n";
print_r($query->getParameters()) . "\n";
?>
--EXPECTF--
DELETE
FROM user
WHERE id = ?
ORDER BY name
LIMIT 1
Array
(
    [0] => 2
)
