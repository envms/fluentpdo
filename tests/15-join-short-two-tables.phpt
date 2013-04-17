--TEST--
join two tables via difference keys
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

$query = $fpdo->from('comment')
		->where('comment.id', 1)
		->leftJoin('user comment_author')->select('comment_author.name AS comment_name')
		->leftJoin('article.user AS article_author')->select('article_author.name AS author_name');
echo $query->getQuery() . "\n";
$result = $query->fetch();
print_r($result);

?>
--EXPECTF--
SELECT comment.*, comment_author.name AS comment_name, article_author.name AS author_name
FROM comment
    LEFT JOIN user AS comment_author ON comment_author.id = comment.user_id
    LEFT JOIN article ON article.id = comment.article_id
    LEFT JOIN user AS article_author ON article_author.id = article.user_id
WHERE comment.id = ?
Array
(
    [id] => 1
    [article_id] => 1
    [user_id] => 2
    [content] => comment 1.1
    [comment_name] => Robert
    [author_name] => Marek
)
