<?php

use PHPUnit\Framework\TestCase;
use Envms\FluentTest\Model\User;
use Envms\FluentPDO\Query;

/**
 * Class UtilitiesTest
 */
class UtilitiesTest extends TestCase {

    /** @var Envms\FluentPDO\Query */
    protected $fluent;

    public function setUp()
    {
        $pdo = new PDO("mysql:dbname=fluentdb;host=localhost", "vagrant","vagrant");

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $pdo->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);

        $this->fluent = new Query($pdo);
    }

    public function testFluentUtil()
    {

        $value  =  Envms\FluentPDO\Utilities::toUpperWords('one');
        $value2 =  Envms\FluentPDO\Utilities::toUpperWords(' one ');
        $value3 =  Envms\FluentPDO\Utilities::toUpperWords('oneTwo');
        $value4 =  Envms\FluentPDO\Utilities::toUpperWords('OneTwo');
        $value5 =  Envms\FluentPDO\Utilities::toUpperWords('oneTwoThree');
        $value6 =  Envms\FluentPDO\Utilities::toUpperWords(' oneTwoThree ');

        self::assertEquals('ONE', $value);
        self::assertEquals('ONE', $value2);
        self::assertEquals('ONE TWO', $value3);
        self::assertEquals('ONE TWO', $value4);
        self::assertEquals('ONE TWO THREE', $value5);
        self::assertEquals('ONE TWO THREE', $value6);

    }

    public function testFormatQuery()
    {
        $query = $this->fluent
            ->from('user')
            ->where('id > ?', 0)
            ->orderBy('name');

        $formattedQuery = Envms\FluentPDO\Utilities::formatQuery($query);

        self::assertEquals('SELECT user.*
FROM user
WHERE id > ?
ORDER BY name', $formattedQuery);

    }

    public function testConvertToNativeType(){
        $query = $this->fluent
            ->from('user')
            ->select(null)
            ->select(array('id'))
            ->where('name', 'Marek')
            ->execute();

        $returnRow = $query->fetch();
        $forceInt = Envms\FluentPDO\Utilities::convertToNativeTypes($query, $returnRow);

        self::assertEquals(['id' => '1'], $returnRow);
        self::assertEquals(['id' => 1], $forceInt);
    }

    public function testisCountable(){
        $selectQuery = $this->fluent
            ->from('user')
            ->select(null)
            ->select(array('id'))
            ->where('name', 'Marek');


        $deleteQuery = $this->fluent
            ->deleteFrom('user')
            ->where('id', 1);

        self::assertEquals(true, Envms\FluentPDO\Utilities::isCountable($selectQuery));
        self::assertEquals(false, Envms\FluentPDO\Utilities::isCountable($deleteQuery));
    }
}