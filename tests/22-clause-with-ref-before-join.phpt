--TEST--
clause with referenced table before join
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

$query = $fpdo->from('article')->select('user.name')->innerJoin('user');
echo $query->getQuery() . "\n";
$query = $fpdo->from('article')->select('author.name')->innerJoin('user as author');
echo $query->getQuery() . "\n";
$query = $fpdo->from('user')->select('article:title')->innerJoin('article:');
echo $query->getQuery() . "\n";
?>
--EXPECTF--
SELECT article.*, user.name
FROM article
    INNER JOIN user ON user.id = article.user_id
SELECT article.*, author.name
FROM article
    INNER JOIN user AS author ON author.id = article.user_id
SELECT user.*, article.title
FROM user
    INNER JOIN article ON article.user_id = user.id
