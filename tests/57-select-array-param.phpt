--TEST--
Accept array of columns in select (no aliases)
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

$query = $fpdo
	->from('user')
	->select(null)
	->select(array('id', 'name'))
	->where('id < ?', 2);

echo $query->getQuery() . "\n";
print_r($query->getParameters());
print_r($query->fetch());
?>
--EXPECTF--
SELECT id, name
FROM user
WHERE id < ?
Array
(
    [0] => 2
)
Array
(
    [id] => 1
    [name] => Marek
)
