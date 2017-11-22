--TEST--
join using USING
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fluent2 Envms\FluentPDO\Query */
$fluent_structure2 = new Envms\FluentPDO\Structure('%s_id', '%s_id');
$fluent2 = new Envms\FluentPDO\Query($pdo, $fluent_structure2);

$query = $fluent2->from('article')
		->innerJoin('user USING (user_id)')
		->select('user.*')
		->getQuery();
echo "$query\n";

$query = $fluent2->from('article')
		->innerJoin('user u USING (user_id)')
		->select('u.*')
		->getQuery();
echo "$query\n";

$query = $fluent2->from('article')
		->innerJoin('user AS u USING (user_id)')
		->select('u.*')
		->getQuery();
echo "$query\n";

unset($fluent_structure2);
unset($fluent2);

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
