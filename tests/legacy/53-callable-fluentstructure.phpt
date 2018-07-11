--TEST--
callable arguments for Envms\FluentPDO\Structure
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var Envms\FluentPDO\Query */

$structure = new Envms\FluentPDO\Structure();
echo $structure->getForeignKey('user') . "\n";
echo $structure->getPrimaryKey('user') . "\n";
$structure = new Envms\FluentPDO\Structure('%s_id', null);
echo $structure->getForeignKey('user') . "\n";
echo $structure->getPrimaryKey('user') . "\n";

$prefix = 'prefix_';
$structure = new Envms\FluentPDO\Structure(function($table) use($prefix) {
    $table = substr($table, 0, strlen($prefix)) == $prefix ? substr($table, strlen($prefix)) : $table;
    return $table.'_id';
}, null);
echo $structure->getForeignKey($prefix.'user') . "\n";
echo $structure->getPrimaryKey($prefix.'user') . "\n";
?>
--EXPECTF--
user_id
id
user_id
user_id
user_id
user_id
