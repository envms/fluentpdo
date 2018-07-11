--TEST--
Basic update
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var Envms\FluentPDO\Query */

$query = $fluent->update('article')->set('published_at', new Envms\FluentPDO\Literal('NOW()'))->where('user_id', 1);
echo $query->getQuery() . "\n";
print_r($query->getParameters()) . "\n";
?>
--EXPECTF--
UPDATE article
SET published_at = NOW()
WHERE user_id = ?
Array
(
    [0] => 1
)
