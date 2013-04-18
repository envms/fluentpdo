--TEST--
Update with ORDER BY and LIMIT
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

$query = $fpdo->update('user')
	->set(array('type' => 'author'))
	->where('id', 2)
	->orderBy('name')
	->limit(1);

echo $query->getQuery() . "\n";
print_r($query->getParameters()) . "\n";
?>
--EXPECTF--
UPDATE user SET type = ?
WHERE id = ?
ORDER BY name
LIMIT 1
Array
(
    [0] => author
    [1] => 2
)
