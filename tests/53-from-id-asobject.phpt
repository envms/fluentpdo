--TEST--
from($table, $id) as stdClass
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

$query = $fpdo->from('user', 2)->asObject();

echo $query->getQuery() . "\n";
print_r($query->fetch());
?>
--EXPECTF--
SELECT user.*
FROM user
WHERE user.id = ?
stdClass Object
(
    [id] => 2
    [country_id] => 1
    [type] => author
    [name] => Robert
)
