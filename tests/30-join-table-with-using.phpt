--TEST--
join using USING
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */
$fluent_structure = new FluentStructure('%s_id', '%s_id');
$fpdo = new FluentPDO($pdo, $fluent_structure);

$query = $fpdo->from('article')
		->innerJoin('user USING (user_id)')
		->select('user.*')
		->getQuery();
echo "$query\n";

$query = $fpdo->from('article')
		->innerJoin('user u USING (user_id)')
		->select('u.*')
		->getQuery();
echo "$query\n";

$query = $fpdo->from('article')
		->innerJoin('user AS u USING (user_id)')
		->select('u.*')
		->getQuery();
echo "$query\n";

?>
--EXPECTF--
SELECT article.*, user.* 
FROM article 
    INNER JOIN user USING (user_id)
SELECT article.*, u.* 
FROM article 
    INNER JOIN user u USING (user_id)
SELECT article.*, u.* 
FROM article 
    INNER JOIN user AS u USING (user_id)
