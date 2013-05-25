--TEST--
callable arguments for FluentStructure
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

$structure = new FluentStructure();
echo $structure->getForeignKey('user') . "\n";
echo $structure->getPrimaryKey('user') . "\n";
$structure = new FluentStructure('%s_id', null);
echo $structure->getForeignKey('user') . "\n";
echo $structure->getPrimaryKey('user') . "\n";

$prefix = 'prefix_';
$structure = new FluentStructure(function($table) use($prefix) {
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
