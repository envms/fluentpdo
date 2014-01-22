<?php


require __DIR__ . '/../vendor/autoload.php';

function connectTo($driver) {

    static $connections = array();

    $driver = strtoupper($driver);

    if (array_key_exists($driver, $connections)) {
        return $connections[$driver];
    }

    $getenv = function ($driver, $key, $default=null) {
        $var = "FLUENTPDO_{$driver}_{$key}";
        if (array_key_exists($var, $_ENV)) {
            return $_ENV[$var];
        }
        if (func_num_args() === 3) {
            return $default;
        }
        throw new PHPUnit_Framework_SkippedTestError("missing env $var");
    };

     $dsn = $getenv($driver, 'DSN');
    $user = $getenv($driver, 'USER', null);
    $pass = $getenv($driver, 'PASS', null);

    return $connections[$driver] = new PDO($dsn, $user, $pass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING,
                                                                     PDO::ATTR_CASE    => PDO::CASE_LOWER));
}

function createTables($driver) {
    $file = __DIR__ . '/' . strtolower($driver) . '.sql';

    if (!file_exists($file)) {
        throw new PHPUnit_Framework_SkippedTestError("missing bootstrap sql: $file");
    }

    $pdo = connectTo($driver);
    $sql = file_get_contents($file);
    return $pdo->exec($sql);
}

function dropTables($driver) {

}
