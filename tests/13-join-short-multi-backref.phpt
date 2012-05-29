--TEST--
multi short join
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

$query = $fpdo->from('article')->innerJoin('comment:user AS comment_user');
echo $query->getQuery() . "\n";
print_r($query->fetch());
?>
--EXPECTF--
SELECT article.*
FROM article
    INNER JOIN comment ON comment.article_id = article.id
    INNER JOIN user AS comment_user ON comment_user.id = comment.user_id
Array
(
    [id] => 1
    [user_id] => 1
    [published_at] => 2011-12-10 12:10:00
    [title] => article 1
    [content] => content 1
)
