--TEST--
Basic update
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

$query = $fpdo->update('country')->set('name', 'aikavolS')->where('id', 1);
$query->execute();
echo $query->getQuery() . "\n";
print_r($query->getParameters()) . "\n";

$query = $fpdo->from('country')->where('id', 1);
print_r($query->fetch());

$fpdo->update('country')->set('name', 'Slovakia')->where('id', 1)->execute();

$query = $fpdo->from('country')->where('id', 1);
print_r($query->fetch());
?>
--EXPECTF--
UPDATE country SET name = ?
WHERE id = ?
Array
(
    [0] => aikavolS
    [1] => 1
)
Array
(
    [id] => 1
    [name] => aikavolS
)
Array
(
    [id] => 1
    [name] => Slovakia
)
