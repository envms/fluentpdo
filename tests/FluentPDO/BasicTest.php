<?php

class BasicTest extends PHPUnit_Framework_TestCase {

    function setUp() {
        createTables('mysql');
    }

    function test() {
        $fpdo = new FluentPDO(connectTo('mysql'));

        // expected values
        $sql = "SELECT user.*\nFROM user\nWHERE id > ?\n    AND name = ?\nORDER BY name";
        $params = array(0, 'Marek');
        $row = array(
            'id' => 1,
            'country_id' => 1,
            'type' => 'admin',
            'name' => 'Marek'
        );

        $query = $fpdo->from('user')
                      ->where('id > ?', 0)
                      ->orderBy('name')
                      ->where('name = ?', 'Marek');

        $this->assertEquals($sql, $query->getQuery());
        $this->assertEquals($params, $query->getParameters());
        $this->assertEquals($row, $query->fetch());
    }

}
