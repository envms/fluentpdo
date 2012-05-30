--TEST--
debug callback
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

/**
 * $fpdo->debug = true;       // log queries to STDERR
 * $fpdo->debug = $callback;  // see below
 */

$fpdo->debug = function($BaseQuery) {
	echo "query: " . $BaseQuery->getQuery(false) . "\n";
	echo "parameters: " . implode(', ', $BaseQuery->getParameters()) . "\n";
	echo "rowCount: " . $BaseQuery->getResult()->rowCount() . "\n";
	// time is impossible to test (each time is other)
	// echo $FluentQuery->getTime() . "\n";
};

$fpdo->from('user')->where('id < ? AND name <> ?', 7, 'Peter')->execute();
$fpdo->debug = null;
?>
--EXPECTF--
query: SELECT user.* FROM user WHERE id < ? AND name <> ?
parameters: 7, Peter
rowCount: 2
