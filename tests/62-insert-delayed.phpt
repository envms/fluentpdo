--TEST--
insert delayed
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

$query = $fpdo->insertInto('article',
		array(
			'user_id' => 1,
			'title' => 'new title',
			'content' => 'new content',
		))->delayed();

echo $query->getQuery() . "\n";
print_r($query->getParameters());

?>
--EXPECTF--
INSERT DELAYED INTO article (user_id, title, content)
VALUES (?, ?, ?)
Array
(
    [0] => 1
    [1] => new title
    [2] => new content
)
