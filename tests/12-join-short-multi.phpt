--TEST--
join same two tables
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

$query = $fpdo->from('comment')->leftJoin('article.user');
echo $query->getQuery() . "\n";
?>
--EXPECTF--
SELECT comment.*
FROM comment
    LEFT JOIN article ON article.id = comment.article_id
    LEFT JOIN user ON user.id = article.user_id
