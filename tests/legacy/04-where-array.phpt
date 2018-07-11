--TEST--
where(array(...))
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var Envms\FluentPDO\Query */

$query = $fluent->from('user')->where(array(
	'id' => 2,
	'type' => 'author',
));

echo $query->getQuery() . "\n";
print_r($query->getParameters());
?>
--EXPECTF--
SELECT user.*
FROM user
WHERE id = ?
    AND type = ?
Array
(
    [0] => 2
    [1] => author
)
