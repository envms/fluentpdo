--TEST--
from($table, $id) as User class
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

class User { public $id, $country_id, $type, $name; }
$query = $fpdo->from('user', 2)->asObject('User');

echo $query->getQuery() . "\n";
print_r($query->fetch());
?>
--EXPECTF--
SELECT user.*
FROM user
WHERE user.id = ?
User Object
(
    [id] => 2
    [country_id] => 1
    [type] => author
    [name] => Robert
)
