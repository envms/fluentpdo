--TEST--
from($table, $id) as User class with arguments
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */
class UserArgument { public $id, $country_id, $type, $name, $argument; public function __construct($argument) { $this->argument = $argument; } }
$query = $fpdo->from('user', 2)->asObject('UserArgument', array('value'));
echo $query->getQuery() . "\n";
print_r($query->fetch());
?>
--EXPECTF--
SELECT user.*
FROM user
WHERE user.id = ?
UserArgument Object
(
    [id] => 2
    [country_id] => 1
    [type] => author
    [name] => Robert
    [argument] => value
)
