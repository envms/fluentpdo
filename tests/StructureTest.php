<?php

require __DIR__ . '/_resources/init.php';

use PHPUnit\Framework\TestCase;
use Envms\FluentPDO\Structure;

/**
 * Class StructureTest
 *
 * @covers \Envms\FluentPDO\Structure
 */
class StructureTest extends TestCase
{

    public function testBasicKey()
    {
        $structure = new Structure();

        self::assertEquals('id', $structure->getPrimaryKey('user'));
        self::assertEquals('user_id', $structure->getForeignKey('user'));
    }

    public function testCustomKey()
    {
        $structure = new Structure('whatAnId', '%s_\xid');

        self::assertEquals('whatAnId', $structure->getPrimaryKey('user'));
        self::assertEquals('user_\xid', $structure->getForeignKey('user'));
    }

    public function testMethodKey()
    {
        $structure = new Structure('id', ['StructureTest', 'suffix']);

        self::assertEquals('id', $structure->getPrimaryKey('user'));
        self::assertEquals('user_id', $structure->getForeignKey('user'));
    }

    /**
     * @param $table
     *
     * @return string
     */
    public static function suffix($table)
    {
        return $table . '_id';
    }

}
