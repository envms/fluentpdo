--TEST--
insert with literal
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var Envms\FluentPDO\Query */

$query = $fluent->insertInto('article',
		array(
			'user_id' => 1,
			'updated_at' => new Envms\FluentPDO\Literal('NOW()'),
			'title' => 'new title',
			'content' => 'new content',
		));

echo $query->getQuery() . "\n";
print_r($query->getParameters());

?>
--EXPECTF--
INSERT INTO article (user_id, updated_at, title, content)
VALUES (?, NOW(), ?, ?)
Array
(
    [0] => 1
    [1] => new title
    [2] => new content
)
