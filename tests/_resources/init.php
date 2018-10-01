<?php

// determine the test environment first
if (getenv('TRAVIS')) {
    $pdo = new PDO("mysql:dbname=fluentdb;host=localhost;charset=utf8", "root");
}
else {
    $pdo = new PDO("mysql:dbname=fluentdb;host=localhost;charset=utf8", "vagrant", "vagrant");
}

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
$pdo->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
