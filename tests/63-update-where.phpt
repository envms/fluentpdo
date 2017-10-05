--TEST--
update where
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

$query = $fpdo->update('users')
    ->set("`users`.`active`", 1)
    ->where("`country`.`name`", 'Slovakia')
    ->where("`users`.`name`", 'Marek');

echo $query->getQuery() . "\n";
print_r($query->getParameters());
$query = $fpdo->update('users')
    ->set("[users].[active]", 1)
    ->where("[country].[name]", 'Slovakia')
    ->where("[users].[name]", 'Marek');

echo $query->getQuery() . "\n";
print_r($query->getParameters());

?>
--EXPECTF--
UPDATE users
    LEFT JOIN country ON country.id = users.country_id
SET `users`.`active` = ?
WHERE `country`.`name` = ?
    AND `users`.`name` = ?
Array
(
    [0] => 1
    [1] => Slovakia
    [2] => Marek
)
UPDATE users
    LEFT JOIN country ON country.id = users.country_id
SET [users].[active] = ?
WHERE [country].[name] = ?
    AND [users].[name] = ?
Array
(
    [0] => 1
    [1] => Slovakia
    [2] => Marek
)
