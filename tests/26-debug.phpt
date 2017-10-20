--TEST--
debug callback
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var Envms\FluentPDO\Query */

/**
 * $fluent->debug = true;       // log queries to STDERR
 * $fluent->debug = $callback;  // see below
 */

$fluent->debug = function($BaseQuery) {
	echo "query: " . $BaseQuery->getQuery(false) . "\n";
	echo "parameters: " . implode(', ', $BaseQuery->getParameters()) . "\n";
	echo "rowCount: " . $BaseQuery->getResult()->rowCount() . "\n";
	// time is impossible to test (each time is other)
	// echo $FluentQuery->getTime() . "\n";
};

$fluent->from('user')->where('id < ? AND name <> ?', 7, 'Peter')->execute();
$fluent->debug = null;
?>
--EXPECTF--
query: SELECT user.* FROM user WHERE id < ? AND name <> ?
parameters: 7, Peter
rowCount: 2
