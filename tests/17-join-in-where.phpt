--TEST--
join in where
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

$query = $fpdo->from('article')->where('comment:content <> "" AND user.country.id = ?', 1);
echo $query->getQuery() . "\n";

?>
--EXPECTF--
SELECT article.*
FROM article
    LEFT JOIN comment ON comment.article_id = article.id
    LEFT JOIN user ON user.id = article.user_id
    LEFT JOIN country ON country.id = user.country_id
WHERE comment.content <> ""
    AND country.id = ?
