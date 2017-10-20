--TEST--
fetch column
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var Envms\FluentPDO\Query */

echo $fluent->from('user', 1)->fetchColumn() . "\n";
echo $fluent->from('user', 1)->fetchColumn(3) . "\n";
if ($fluent->from('user', 3)->fetchColumn() === false) echo "false\n";
if ($fluent->from('user', 3)->fetchColumn(3) === false) echo "false\n";

?>
--EXPECTF--
1
Marek
false
false
