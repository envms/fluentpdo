--TEST--
join in where
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

$query = $fpdo->from('article')->groupBy('user.type')
		->select(null)->select('user.type, count(article.id) as article_count');
echo $query->getQuery() . "\n";
$result = $query->fetchAll();
print_r($result);
?>
--EXPECTF--
SELECT user.type, count(article.id) as article_count
FROM article
    LEFT JOIN user ON user.id = article.user_id
GROUP BY user.type
Array
(
    [0] => Array
        (
            [type] => admin
            [article_count] => 2
        )

    [1] => Array
        (
            [type] => author
            [article_count] => 1
        )

)
