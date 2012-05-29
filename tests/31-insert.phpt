--TEST--
insert
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

$query = $fpdo->insertInto('article',
		array(
			'user_id' => 1,
			'title' => 'new title',
			'content' => 'new content',
		));

echo $query->getQuery() . "\n";
echo 'last_inserted_id = ' . $query->execute() . "\n";

$fpdo->getPdo()->query('DELETE FROM article WHERE id > 3')->execute();
$fpdo->getPdo()->query('ALTER TABLE article AUTO_INCREMENT=4')->execute();

?>
--EXPECTF--
INSERT INTO article (user_id, title, content) 
VALUES (1, 'new title', 'new content')
last_inserted_id = 4
