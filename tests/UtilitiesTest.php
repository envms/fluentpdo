<?php

require __DIR__ . '/_resources/init.php';

use PHPUnit\Framework\TestCase;
use Envms\FluentPDO\{Query,Utilities};

/**
 * Class UtilitiesTest
 */
class UtilitiesTest extends TestCase
{

    /** @var Envms\FluentPDO\Query */
    protected $fluent;

    public function setUp(): void
    {
        global $pdo;

        $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_BOTH);

        $this->fluent = new Query($pdo);
    }

    public function testFluentUtil()
    {

        $value = Utilities::toUpperWords('one');
        $value2 = Utilities::toUpperWords(' one ');
        $value3 = Utilities::toUpperWords('oneTwo');
        $value4 = Utilities::toUpperWords('OneTwo');
        $value5 = Utilities::toUpperWords('oneTwoThree');
        $value6 = Utilities::toUpperWords(' oneTwoThree ');

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

        $formattedQuery = Utilities::formatQuery($query);

        self::assertEquals("SELECT user.*\nFROM user\nWHERE id > ?\nORDER BY name", $formattedQuery);
    }

    public function testConvertToNativeType()
    {
        $query = $this->fluent
            ->from('user')
            ->select(null)
            ->select(['id'])
            ->where('name', 'Marek')
            ->execute();

        $returnRow = $query->fetch();
        $forceInt = Utilities::stringToNumeric($query, $returnRow);

        self::assertEquals(['id' => '1'], $returnRow);
        self::assertEquals(['id' => 1], $forceInt);
    }

    public function testConvertSqlWriteValues()
    {
        $valueArray = Utilities::convertSqlWriteValues(['string', 1, 2, false, true, null, 'false']);
        $value1 = Utilities::convertSqlWriteValues(false);
        $value2 = Utilities::convertSqlWriteValues(true);

        self::assertEquals(['string', 1, 2, 0, 1, null, 'false'], $valueArray);
        self::assertEquals(0, $value1);
        self::assertEquals(1, $value2);
    }

    public function testisCountable()
    {
        $selectQuery = $this->fluent
            ->from('user')
            ->select(null)
            ->select(['id'])
            ->where('name', 'Marek');

        $deleteQuery = $this->fluent
            ->deleteFrom('user')
            ->where('id', 1);

        self::assertEquals(true, Utilities::isCountable($selectQuery));
        self::assertEquals(false, Utilities::isCountable($deleteQuery));
    }

}