--TEST--
join in where
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

$query = $fpdo->from('article')->orderBy('user.name, article.title');
echo $query->getQuery() . "\n";

?>
--EXPECTF--
SELECT article.*
FROM article
    LEFT JOIN user ON user.id = article.user_id
ORDER BY user.name, article.title
