<?php
require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
$pdo->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
$script = __DIR__ . '/fluentdb.sql';
$pdo->exec(file_get_contents($script));
