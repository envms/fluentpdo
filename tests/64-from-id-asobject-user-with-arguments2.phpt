--TEST--
from($table, $id) as User class with arguments
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */
class UserArgument2 { public $id, $country_id, $type, $name; public function __construct($name) { $this->name = $name; } }
$query = $fpdo->from('user', 2)->asObject('UserArgument2', array('Michael'));
echo $query->getQuery() . "\n";
print_r($query->fetch());
?>
--EXPECTF--
SELECT user.*
FROM user
WHERE user.id = ?
UserArgument2 Object
(
    [id] => 2
    [country_id] => 1
    [type] => author
    [name] => Michael
)
