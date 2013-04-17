--TEST--
full join
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

$query = $fpdo->from('article')
		->select('user.name')
		->leftJoin('user ON user.id = article.user_id')
		->orderBy('article.title');

echo $query->getQuery() . "\n";
foreach ($query as $row) {
	echo "$row[name] - $row[title]\n";
}
?>
--EXPECTF--
SELECT article.*, user.name
FROM article
    LEFT JOIN user ON user.id = article.user_id
ORDER BY article.title
Marek - article 1
Robert - article 2
Marek - article 3
