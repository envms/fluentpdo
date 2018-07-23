<?php

use PHPUnit\Framework\TestCase;
include "connect.inc.php";

class QueryTest extends TestCase {

    public function testBasicQuery() {
        $query = $fluent
            ->from('user')
            ->where('id > ?', 0)
            ->orderBy('name')
            ->where('name = ?', 'Marek');
        $result = $query->fetch();

        self::assertArraySimilar([['id'=> 1],['country_id'=> 1], ["type"=> 'admin'],["name" => 'Mark']], $result);
    }

    public function testQueryWithHaving(){
        $query = $fluent
            ->from('user')
            ->select(null)
            ->select('type, count(id) AS type_count')
            ->where('id > ?', 1)
            ->groupBy('type')
            ->having('type_count > ?', 1)
            ->orderBy('name');

        $result = $query->getQuery();

        self::assertEquals("SELECT type, count(id) AS type_count FROM user WHERE id > ? GROUP BY type HAVING type_count > ? ORDER BY name", $result);
    }
}