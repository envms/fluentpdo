--TEST--
Basic operations
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";

echo "'" . \FluentPDO\FluentUtils::toUpperWords('one') . "'\n";
echo "'" . \FluentPDO\FluentUtils::toUpperWords(' one ') . "'\n";
echo "'" . \FluentPDO\FluentUtils::toUpperWords('oneTwo') . "'\n";
echo "'" . \FluentPDO\FluentUtils::toUpperWords('OneTwo') . "'\n";
echo "'" . \FluentPDO\FluentUtils::toUpperWords('oneTwoThree') . "'\n";
echo "'" . \FluentPDO\FluentUtils::toUpperWords(' oneTwoThree  ') . "'\n";

?>
--EXPECTF--
'ONE'
'ONE'
'ONE TWO'
'ONE TWO'
'ONE TWO THREE'
'ONE TWO THREE'
