--TEST--
join in where
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

$query = $fpdo->from('article')->select('user.name as author');
echo $query->getQuery() . "\n";

?>
--EXPECTF--
SELECT article.*, user.name as author
FROM article
    LEFT JOIN user ON user.id = article.user_id
