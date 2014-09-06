--TEST--
Countable interface (doesn't break prev query)
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

$articles = $fpdo
	->from('article')
	->select(NULL)
	->select('title')
	->where('id > 1');

echo count($articles) . "\n";
print_r($articles->fetchAll());
?>
--EXPECTF--
2
Array
(
    [0] => Array
        (
            [title] => article 2
        )

    [1] => Array
        (
            [title] => article 3
        )

)
