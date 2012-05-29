--TEST--
short join - default join is left join
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

$query = $fpdo->from('article')->leftJoin('user');
echo $query->getQuery() . "\n";
$query = $fpdo->from('article')->leftJoin('user author');
echo $query->getQuery() . "\n";
$query = $fpdo->from('article')->leftJoin('user AS author');
echo $query->getQuery() . "\n";

?>
--EXPECTF--
SELECT article.*
FROM article
    LEFT JOIN user ON user.id = article.user_id
SELECT article.*
FROM article
    LEFT JOIN user AS author ON author.id = article.user_id
SELECT article.*
FROM article
    LEFT JOIN user AS author ON author.id = article.user_id
