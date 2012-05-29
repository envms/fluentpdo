--TEST--
short join back reference
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

$query = $fpdo->from('user')->innerJoin('article:');
echo $query->getQuery() . "\n";
$query = $fpdo->from('user')->innerJoin('article: with_articles');
echo $query->getQuery() . "\n";
$query = $fpdo->from('user')->innerJoin('article: AS with_articles');
echo $query->getQuery() . "\n";
?>
--EXPECTF--
SELECT user.*
FROM user
    INNER JOIN article ON article.user_id = user.id
SELECT user.*
FROM user
    INNER JOIN article AS with_articles ON with_articles.user_id = user.id
SELECT user.*
FROM user
    INNER JOIN article AS with_articles ON with_articles.user_id = user.id
