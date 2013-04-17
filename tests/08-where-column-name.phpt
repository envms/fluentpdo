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
print_r($query->getParameters());
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
Array
(
    [:type] => author
    [:id] => 1
    [:name] => Marek
)
Robert
