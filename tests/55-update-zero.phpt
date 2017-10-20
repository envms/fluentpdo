--TEST--
Update with zero value.
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var Envms\FluentPDO\Query */

$fluent->update('article')->set('content', '')->where('id', 1)->execute();
$user = $fluent->from('article')->where('id', 1)->fetch();

echo 'ID: ' . $user['id'] . ' - content: ' . $user['content'] . "\n";
$fluent->update('article')->set('content', 'content 1')->where('id', 1)->execute();
$user = $fluent->from('article')->where('id', 1)->fetch();
echo 'ID: ' . $user['id'] . ' - content: ' . $user['content'] . "\n";
?>
--EXPECTF--
ID: 1 - content: 
ID: 1 - content: content 1
