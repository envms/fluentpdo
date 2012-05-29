--TEST--
join using USING
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo2 FluentPDO */
$fluent_structure2 = new FluentStructure('%s_id', '%s_id');
$fpdo2 = new FluentPDO($pdo, $fluent_structure2);

$query = $fpdo2->from('article')
		->innerJoin('user USING (user_id)')
		->select('user.*')
		->getQuery();
echo "$query\n";

$query = $fpdo2->from('article')
		->innerJoin('user u USING (user_id)')
		->select('u.*')
		->getQuery();
echo "$query\n";

$query = $fpdo2->from('article')
		->innerJoin('user AS u USING (user_id)')
		->select('u.*')
		->getQuery();
echo "$query\n";

unset($fluent_structure2);
unset($fpdo2);

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
