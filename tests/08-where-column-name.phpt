--TEST--
where with named :params
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

$query = $fpdo->from('user')
		->where('type = :type', array(':type' => 'author'))
		->where('id > :id AND name <> :name', array(':id' => 1, ':name' => 'Marek'));

echo $query->getQuery() . "\n";
var_dump($query->getParameters());
foreach ($query as $row) {
	echo "$row[name]\n";
}
?>
--EXPECTF--
SELECT user.* 
FROM user 
WHERE type = :type 
    AND id > :id 
    AND name <> :name
array(3) {
  [":type"]=>
  string(6) "author"
  [":id"]=>
  int(1)
  [":name"]=>
  string(5) "Marek"
}
Robert
