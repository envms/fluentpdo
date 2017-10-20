--TEST--
fetch 
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var Envms\FluentPDO\Query */

echo $fluent->from('user', 1)->fetch('name') . "\n";
print_r($fluent->from('user', 1)->fetch());
if ($fluent->from('user', 3)->fetch() === false) echo "false\n";
if ($fluent->from('user', 3)->fetch('name') === false) echo "false\n";

?>
--EXPECTF--
Marek
Array
(
    [id] => 1
    [country_id] => 1
    [type] => admin
    [name] => Marek
)
false
false
