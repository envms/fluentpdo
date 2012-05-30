--TEST--
FROM with alias
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

$query = $fpdo->from('user author')->getQuery();
echo "$query\n";
$query = $fpdo->from('user AS author')->getQuery();
echo "$query\n";
$query = $fpdo->from('user AS author', 1)->getQuery();
echo "$query\n";
$query = $fpdo->from('user AS author')->select('country.name')->getQuery();
echo "$query\n";

?>
--EXPECTF--
SELECT author.*
FROM user author
SELECT author.*
FROM user AS author
SELECT author.*
FROM user AS author
WHERE author.id = ?
SELECT author.*, country.name
FROM user AS author
    LEFT JOIN country ON country.id = user AS author.country_id
