--TEST--
Update with smart join
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

$query = $fpdo->update('user')
	->set(array('type' => 'author'))
	->where('country.id', 1);

echo $query->getQuery() . "\n";
print_r($query->getParameters()) . "\n";
?>
--EXPECTF--
UPDATE user
    LEFT JOIN country ON country.id = user.country_id SET type = ?
WHERE country.id = ?
Array
(
    [0] => author
    [1] => 1
)
