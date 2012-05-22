--TEST--
fetch all with params
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

$result = $fpdo->from('user')->fetchAll('id', 'type, name');
print_r($result);

?>
--EXPECTF--
Array
(
    [1] => Array
        (
            [id] => 1
            [type] => admin
            [name] => Marek
        )

    [2] => Array
        (
            [id] => 2
            [type] => author
            [name] => Robert
        )

)
