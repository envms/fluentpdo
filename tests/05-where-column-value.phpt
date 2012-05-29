--TEST--
where('column', 'value')
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

$query = $fpdo->from('user')->where('type', 'author');

echo $query->getQuery() . "\n";
print_r($query->getParameters());
?>
--EXPECTF--
SELECT user.*
FROM user
WHERE type = ?
Array
(
    [0] => author
)
