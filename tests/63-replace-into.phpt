--TEST--
replace into
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

$query = $fpdo->replaceInto('comment',
		array(
        'id' => 1,
        'article_id' => 1,
        'user_id' => 1,
        'content' => 'new text',
		));

echo $query->getQuery() . "\n";
print_r($query->getParameters()) . "\n";
?>
--EXPECTF--
REPLACE INTO comment SET id = ?, article_id = ?, user_id = ?, content = ?
Array
(
    [0] => 1
    [1] => 1
    [2] => 1
    [3] => new text
)
