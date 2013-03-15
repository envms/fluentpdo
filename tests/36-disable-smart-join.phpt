--TEST--
Disable and enable smart join feature
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

$query = $fpdo->from('comment')
		->select('user.name')
		->orderBy('article.published_at')
		->getQuery();
echo "-- Plain:\n$query\n\n";

$query = $fpdo->from('comment')
		->select('user.name')
		->disableSmartJoin()
		->orderBy('article.published_at')
		->getQuery();
echo "-- Disable:\n$query\n\n";

$query = $fpdo->from('comment')
		->disableSmartJoin()
		->select('user.name')
		->enableSmartJoin()
		->orderBy('article.published_at')
		->getQuery();
echo "-- Disable and enable:\n$query\n\n";

?>
--EXPECTF--
-- Plain:
SELECT comment.*, user.name
FROM comment
    LEFT JOIN user ON user.id = comment.user_id
    LEFT JOIN article ON article.id = comment.article_id
ORDER BY article.published_at

-- Disable:
SELECT comment.*, user.name
FROM comment
ORDER BY article.published_at

-- Disable and enable:
SELECT comment.*, user.name
FROM comment
    LEFT JOIN user ON user.id = comment.user_id
    LEFT JOIN article ON article.id = comment.article_id
ORDER BY article.published_at

