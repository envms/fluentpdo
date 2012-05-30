--TEST--
insert with literal
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

$query = $fpdo->insertInto('article',
		array(
			'user_id' => 1,
			'updated_at' => new FluentLiteral('NOW()'),
			'title' => 'new title',
			'content' => 'new content',
		));

echo $query->getQuery() . "\n";

?>
--EXPECTF--
INSERT INTO article (user_id, updated_at, title, content)
VALUES (1, NOW(), 'new title', 'new content')
