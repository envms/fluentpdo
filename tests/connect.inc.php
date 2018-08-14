<?php
error_reporting(E_ALL | E_STRICT);

include dirname(__FILE__) . "/../src/Queries/Base.php";
include dirname(__FILE__) . "/../src/Queries/Common.php";
include dirname(__FILE__) . "/../src/Queries/Insert.php";
include dirname(__FILE__) . "/../src/Queries/Select.php";
include dirname(__FILE__) . "/../src/Queries/Update.php";
include dirname(__FILE__) . "/../src/Queries/Delete.php";
include dirname(__FILE__) . "/../src/Literal.php";
include dirname(__FILE__) . "/../src/Structure.php";
include dirname(__FILE__) . "/../src/Utilities.php";
include dirname(__FILE__) . "/../src/Query.php";

use Envms\FluentPDO\Query;


function initiateFluent() {

    $pdo = new PDO("mysql:dbname=fluentdb;host=localhost", "vagrant","vagrant");

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $pdo->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
    return $fluent = new Query($pdo);
}

