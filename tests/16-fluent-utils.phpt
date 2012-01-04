--TEST--
Basic operations
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";

echo "'" . FluentUtils::toUpperWords('one') . "'\n";
echo "'" . FluentUtils::toUpperWords(' one ') . "'\n";
echo "'" . FluentUtils::toUpperWords('oneTwo') . "'\n";
echo "'" . FluentUtils::toUpperWords('OneTwo') . "'\n";
echo "'" . FluentUtils::toUpperWords('oneTwoThree') . "'\n";
echo "'" . FluentUtils::toUpperWords(' oneTwoThree  ') . "'\n";

?>
--EXPECTF--
'ONE'
'ONE'
'ONE TWO'
'ONE TWO'
'ONE TWO THREE'
'ONE TWO THREE'
