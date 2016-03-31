--TEST--
incorrect select and error retrieval.
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";
/* @var $fpdo FluentPDO */

$query = $fpdo->from('foobar') // Foobar doesn't exist.
            ->where('foo = ?', 'bar');
$result = $query->execute();

if(!$result) {
    echo "Error\n";
}

echo $query->getLastError()->getMessage() . "\n";
echo $query->getLastError()->getQuery() . "\n";
?>
--EXPECTF--
Error
Table 'fblog.foobar' doesn't exist
SELECT foobar.*
FROM foobar
WHERE foo = 'bar'
