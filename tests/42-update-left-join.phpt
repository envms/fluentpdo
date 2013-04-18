--TEST--
Basic update
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

$query = $fpdo->update('user')
	->outerJoin('country ON country.id = user.country_id')
	->set(array('name' => 'keraM', '`type`' => 'author'))
	->where('id', 1);

echo $query->getQuery() . "\n";
print_r($query->getParameters()) . "\n";
?>
--EXPECTF--
UPDATE user OUTER JOIN country ON country.id = user.country_id SET name = ?, `type` = ?
WHERE id = ?
Array
(
    [0] => keraM
    [1] => author
    [2] => 1
)
