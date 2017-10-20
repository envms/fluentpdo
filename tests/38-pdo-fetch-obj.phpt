--TEST--
PDO::FETCH_OBJ option.
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var Envms\FluentPDO\Query */


$query = $fluent->from('user')->where('id > ?', 0)->orderBy('name');
$query = $query->where('name = ?', 'Marek');
$fluent->getPdo()->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

print_r($query->getParameters());
print_r($query->fetch());

// Set back for other tests.
$fluent->getPdo()->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_BOTH);
?>
--EXPECTF--
Array
(
    [0] => 0
    [1] => Marek
)
stdClass Object
(
    [id] => 1
    [country_id] => 1
    [type] => admin
    [name] => Marek
)
