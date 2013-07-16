--TEST--
Update with zero value.
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

$fpdo->update('article')->set('content', '')->where('id', 1)->execute();
$user = $fpdo->from('article')->where('id', 1)->fetch();

echo 'ID: ' . $user['id'] . ' - content: ' . $user['content'] . "\n";
$fpdo->update('article')->set('content', 'content 1')->where('id', 1)->execute();
$user = $fpdo->from('article')->where('id', 1)->fetch();
echo 'ID: ' . $user['id'] . ' - content: ' . $user['content'] . "\n";
?>
--EXPECTF--
ID: 1 - content: 
ID: 1 - content: content 1
