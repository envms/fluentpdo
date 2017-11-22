--TEST--
Basic operations
--FILE--
<?php
include_once dirname(__FILE__) . "/connect.inc.php";

echo "'" . Envms\FluentPDO\Utilities::toUpperWords('one') . "'\n";
echo "'" . Envms\FluentPDO\Utilities::toUpperWords(' one ') . "'\n";
echo "'" . Envms\FluentPDO\Utilities::toUpperWords('oneTwo') . "'\n";
echo "'" . Envms\FluentPDO\Utilities::toUpperWords('OneTwo') . "'\n";
echo "'" . Envms\FluentPDO\Utilities::toUpperWords('oneTwoThree') . "'\n";
echo "'" . Envms\FluentPDO\Utilities::toUpperWords(' oneTwoThree  ') . "'\n";

?>
--EXPECTF--
'ONE'
'ONE'
'ONE TWO'
'ONE TWO'
'ONE TWO THREE'
'ONE TWO THREE'
