--TEST--
update where
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

$query = $fpdo->update('users')
    ->set("`users`.`active`", 1)
    ->where("`confirm`.`key`", 123)
    ->where("`users`.`email`", 123);


echo $query->getQuery() . "\n";
print_r($query->getParameters());
$query = $fpdo->update('users')
    ->set("[users].[active]", 1)
    ->where("[confirm].[key]", 123)
    ->where("[users].[email]", 123);


echo $query->getQuery() . "\n";
print_r($query->getParameters());

?>
--EXPECTF--
UPDATE users SET `users`.`active` = ?
WHERE `confirm`.`key` = ?
    AND `users`.`email` = ?
Array
(
    [0] => 1
    [1] => 123
    [2] => 123
)
UPDATE users SET [users].[active] = ?
WHERE [confirm].[key] = ?
    AND [users].[email] = ?
Array
(
    [0] => 1
    [1] => 123
    [2] => 123
)
